<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as Pdf;
use ZipArchive;

class BackupController extends Controller
{
    /**
     * Download a full backup for a doctor.
     * Doctor: downloads their own data.
     * Admin: can specify ?doctor_id=X to download any doctor's data.
     */
    public function download()
    {
        $authUser = auth()->user();

        // Determine which doctor to back up
        if ($authUser->isAdmin() && request('doctor_id')) {
            $doctor = User::findOrFail(request('doctor_id'));
        } elseif ($authUser->isDoctor()) {
            $doctor = $authUser;
        } elseif ($authUser->isSecretary()) {
            $doctor = $authUser->assignedDoctor;
        } else {
            abort(403, 'Unauthorized action.');
        }

        if (!$doctor) {
            abort(404, 'Doctor not found.');
        }

        $clinicName = \App\Models\Setting::get('clinic_name', 'Clinova');

        // Load all patients with all related data (including soft-deleted)
        $patients = Patient::withTrashed()
            ->where('doctor_id', $doctor->id)
            ->with([
                'visits' => fn($q) => $q->withTrashed(),
                'files' => fn($q) => $q->withTrashed(),
                'appointments' => fn($q) => $q->withTrashed(),
            ])
            ->get();

        // Create temp ZIP file
        $zipFileName = 'backup_' . str_replace(' ', '_', $doctor->name) . '_' . date('Y-m-d_H-i-s') . '.zip';
        $tempZipPath = storage_path('app/temp/' . $zipFileName);

        // Ensure temp directory exists
        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create backup file.');
        }

        foreach ($patients as $patient) {
            // Sanitize folder name
            $folderName = $this->sanitizeName($patient->name) . '_' . $patient->id;

            // Generate PDF record for this patient
            $pdfContent = $this->generatePatientPDF($patient, $doctor, $clinicName);
            $zip->addFromString("{$folderName}/سجل_المريض.pdf", $pdfContent);

            // Add standalone medical files
            foreach ($patient->files as $file) {
                $this->addFileToZip($zip, $file->file_path, $folderName, $file->file_name);
            }

            // Add visit attachment files
            foreach ($patient->visits as $visit) {
                if ($visit->treatment_file_path) {
                    $visitFileName = 'زيارة_' . $visit->created_at->format('Y-m-d') . '_' . basename($visit->treatment_file_path);
                    $this->addFileToZip($zip, $visit->treatment_file_path, $folderName, $visitFileName);
                }
            }
        }

        $zip->close();

        // Send file and delete after
        return response()->download($tempZipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Generate a PDF for a single patient's full record.
     */
    private function generatePatientPDF(Patient $patient, User $doctor, string $clinicName): string
    {
        $pdf = Pdf::loadView('pdf.patient-record', [
            'patient' => $patient,
            'doctorName' => __('Dr.') . ' ' . $doctor->name,
            'clinicName' => $clinicName,
            'exportDate' => now()->format('Y-m-d H:i'),
        ]);

        return $pdf->output();
    }

    /**
     * Add a file from storage to the ZIP archive.
     */
    private function addFileToZip(ZipArchive $zip, string $storagePath, string $folder, string $fileName): void
    {
        $disk = Storage::disk('public');

        if ($disk->exists($storagePath)) {
            $fullPath = $disk->path($storagePath);

            // Check if file is gzip compressed (our system compresses uploads)
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $fullPath);
            finfo_close($finfo);

            if ($mimeType === 'application/gzip' || $mimeType === 'application/x-gzip') {
                // Decompress and add
                $content = @gzdecode(file_get_contents($fullPath));
                if ($content !== false) {
                    $zip->addFromString("{$folder}/ملفات/{$fileName}", $content);
                } else {
                    // If decompression fails, add as-is
                    $zip->addFile($fullPath, "{$folder}/ملفات/{$fileName}");
                }
            } else {
                $zip->addFile($fullPath, "{$folder}/ملفات/{$fileName}");
            }
        }
    }

    /**
     * Sanitize a name for use as a folder name.
     */
    private function sanitizeName(string $name): string
    {
        // Remove characters not allowed in folder names but keep Arabic
        $name = preg_replace('/[\/\\\\:*?"<>|]/', '', $name);
        $name = trim($name);
        return $name ?: 'unknown';
    }

    /**
     * Download a full database SQL dump for Admin (phpMyAdmin-compatible format).
     */
    public function databaseBackup()
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $sqlFileName = 'database_backup_' . date('Y-m-d_H-i-s') . '.sql';
        $tempSqlPath = storage_path('app/temp/' . $sqlFileName);

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        try {
            $this->generatePhpMyAdminStyleDump($tempSqlPath);
        } catch (\Exception $e) {
            abort(500, 'Database export failed: ' . $e->getMessage());
        }

        return response()->download($tempSqlPath, $sqlFileName, [
            'Content-Type' => 'application/sql',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download a full system backup (Database SQL + All stored files) as ZIP.
     */
    public function fullSystemBackup()
    {
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $zipFileName = 'system_backup_' . date('Y-m-d_H-i-s') . '.zip';
        $tempZipPath = storage_path('app/temp/' . $zipFileName);

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create backup file.');
        }

        // 1) Generate and add the database SQL dump
        $tempSqlPath = storage_path('app/temp/db_dump_' . uniqid() . '.sql');
        try {
            $this->generatePhpMyAdminStyleDump($tempSqlPath);
            $zip->addFile($tempSqlPath, 'database/clinova.sql');
        } catch (\Exception $e) {
            $zip->close();
            @unlink($tempSqlPath);
            abort(500, 'Database export failed: ' . $e->getMessage());
        }

        // 2) Add all storage files (patient files, treatments, profile images, etc.)
        $storagePath = Storage::disk('public')->path('');
        $storageDirs = ['patient_files', 'patient-files', 'patients', 'profile-images', 'treatments', 'visit-attachments'];

        foreach ($storageDirs as $dir) {
            $dirFullPath = $storagePath . DIRECTORY_SEPARATOR . $dir;
            if (is_dir($dirFullPath)) {
                $this->addDirectoryToZip($zip, $dirFullPath, 'storage/' . $dir);
            }
        }

        $zip->close();

        // Clean up temp SQL file
        @unlink($tempSqlPath);

        return response()->download($tempZipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Recursively add a directory's files to a ZipArchive.
     */
    private function addDirectoryToZip(ZipArchive $zip, string $dirPath, string $zipFolder): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dirPath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filePath = $file->getRealPath();
                $relativePath = $zipFolder . '/' . substr($filePath, strlen($dirPath) + 1);
                // Normalize directory separators for ZIP
                $relativePath = str_replace('\\', '/', $relativePath);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    /**
     * Generate a phpMyAdmin-compatible SQL dump file.
     * Produces output with: column names in INSERT, separate ALTER TABLE for indexes/constraints,
     * START TRANSACTION/COMMIT, proper header/footer matching phpMyAdmin export format.
     */
    private function generatePhpMyAdminStyleDump(string $outputPath): void
    {
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '3306');
        $dbName = env('DB_DATABASE');
        $dbUser = env('DB_USERNAME');
        $dbPass = env('DB_PASSWORD');

        if (!$dbName || !$dbUser) {
            throw new \Exception('Database credentials not found.');
        }

        $pdo = new \PDO("mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4", $dbUser, $dbPass, [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
        ]);

        $pdo->exec("SET NAMES utf8mb4");

        $serverVersion = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        $phpVersion = phpversion();
        $generationTime = date('M d, Y \a\t h:i A');

        $handle = fopen($outputPath, 'w');
        if (!$handle) {
            throw new \Exception("Cannot write to file: {$outputPath}");
        }

        // Header
        fwrite($handle, "-- phpMyAdmin SQL Dump\n");
        fwrite($handle, "-- version 5.2.1\n");
        fwrite($handle, "-- https://www.phpmyadmin.net/\n");
        fwrite($handle, "--\n");
        fwrite($handle, "-- Host: {$dbHost}\n");
        fwrite($handle, "-- Generation Time: {$generationTime}\n");
        fwrite($handle, "-- Server version: {$serverVersion}\n");
        fwrite($handle, "-- PHP Version: {$phpVersion}\n");
        fwrite($handle, "\n");
        fwrite($handle, "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n");
        fwrite($handle, "START TRANSACTION;\n");
        fwrite($handle, "SET time_zone = \"+00:00\";\n");
        fwrite($handle, "\n\n");
        fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n");
        fwrite($handle, "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n");
        fwrite($handle, "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n");
        fwrite($handle, "/*!40101 SET NAMES utf8mb4 */;\n");
        fwrite($handle, "\n");
        fwrite($handle, "--\n");
        fwrite($handle, "-- Database: `{$dbName}`\n");
        fwrite($handle, "--\n\n");

        // Get all tables (not views)
        $tables = [];
        $stmt = $pdo->query("SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
        while ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $tables[] = $row[0];
        }
        $stmt->closeCursor();

        // Collect all indexes and constraints for later
        $allIndexes = [];
        $allAutoIncrements = [];
        $allConstraints = [];

        foreach ($tables as $table) {
            fwrite($handle, "-- --------------------------------------------------------\n\n");
            fwrite($handle, "--\n");
            fwrite($handle, "-- Table structure for table `{$table}`\n");
            fwrite($handle, "--\n\n");

            // Get CREATE TABLE statement
            $createStmtQuery = $pdo->query("SHOW CREATE TABLE `{$table}`");
            $createStmt = $createStmtQuery->fetch(\PDO::FETCH_ASSOC);
            $createStmtQuery->closeCursor();
            $createSql = $createStmt['Create Table'];

            // Parse the CREATE TABLE to extract a clean version without indexes/constraints
            $cleanCreate = $this->extractCleanCreateTable($createSql, $table, $allIndexes, $allAutoIncrements, $allConstraints);
            fwrite($handle, $cleanCreate . "\n\n");

            // Dump data
            $countStmtQuery = $pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $rowCount = $countStmtQuery->fetchColumn();
            $countStmtQuery->closeCursor();

            if ($rowCount > 0) {
                fwrite($handle, "--\n");
                fwrite($handle, "-- Dumping data for table `{$table}`\n");
                fwrite($handle, "--\n\n");

                // Get column names
                $colStmtQuery = $pdo->query("SHOW COLUMNS FROM `{$table}`");
                $columns = [];
                while ($col = $colStmtQuery->fetch(\PDO::FETCH_ASSOC)) {
                    $columns[] = $col['Field'];
                }
                $colStmtQuery->closeCursor();
                $columnList = implode('`, `', $columns);

                // Fetch data
                $dataStmtQuery = $pdo->query("SELECT * FROM `{$table}`");
                $rows = $dataStmtQuery->fetchAll(\PDO::FETCH_ASSOC);
                $dataStmtQuery->closeCursor();

                if (!empty($rows)) {
                    fwrite($handle, "INSERT INTO `{$table}` (`{$columnList}`) VALUES\n");

                    $lastIndex = count($rows) - 1;
                    foreach ($rows as $i => $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $pdo->quote($value);
                            }
                        }
                        $line = '(' . implode(', ', $values) . ')';
                        if ($i < $lastIndex) {
                            $line .= ',';
                        } else {
                            $line .= ';';
                        }
                        fwrite($handle, $line . "\n");
                    }
                }
            }

            fwrite($handle, "\n");
        }

        // Write indexes section
        fwrite($handle, "--\n");
        fwrite($handle, "-- Indexes for dumped tables\n");
        fwrite($handle, "--\n\n");

        foreach ($allIndexes as $table => $indexes) {
            if (!empty($indexes)) {
                fwrite($handle, "--\n");
                fwrite($handle, "-- Indexes for table `{$table}`\n");
                fwrite($handle, "--\n");
                fwrite($handle, "ALTER TABLE `{$table}`\n");
                fwrite($handle, implode(",\n", array_map(fn($idx) => "  {$idx}", $indexes)) . ";\n\n");
            }
        }

        // Write AUTO_INCREMENT section
        fwrite($handle, "--\n");
        fwrite($handle, "-- AUTO_INCREMENT for dumped tables\n");
        fwrite($handle, "--\n\n");

        foreach ($allAutoIncrements as $table => $aiInfo) {
            fwrite($handle, "--\n");
            fwrite($handle, "-- AUTO_INCREMENT for table `{$table}`\n");
            fwrite($handle, "--\n");
            fwrite($handle, "ALTER TABLE `{$table}`\n");
            fwrite($handle, "  MODIFY {$aiInfo};\n\n");
        }

        // Write constraints section
        fwrite($handle, "--\n");
        fwrite($handle, "-- Constraints for dumped tables\n");
        fwrite($handle, "--\n\n");

        foreach ($allConstraints as $table => $constraints) {
            if (!empty($constraints)) {
                fwrite($handle, "--\n");
                fwrite($handle, "-- Constraints for table `{$table}`\n");
                fwrite($handle, "--\n");
                fwrite($handle, "ALTER TABLE `{$table}`\n");
                fwrite($handle, implode(",\n", array_map(fn($c) => "  {$c}", $constraints)) . ";\n\n");
            }
        }

        // Footer
        fwrite($handle, "COMMIT;\n\n");
        fwrite($handle, "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n");
        fwrite($handle, "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n");
        fwrite($handle, "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n");

        fclose($handle);
    }

    /**
     * Parse CREATE TABLE statement to extract clean structure, indexes, auto_increment, and constraints.
     * Returns a clean CREATE TABLE with only column definitions (like phpMyAdmin).
     */
    private function extractCleanCreateTable(string $createSql, string $table, array &$allIndexes, array &$allAutoIncrements, array &$allConstraints): string
    {
        $lines = explode("\n", $createSql);
        $cleanLines = [];
        $indexes = [];
        $constraints = [];
        $autoIncrementCol = null;

        $firstLine = array_shift($lines); // CREATE TABLE `xxx` (
        $lastLine = array_pop($lines);    // ) ENGINE=InnoDB ...

        foreach ($lines as $line) {
            $trimmed = trim($line);
            // Remove trailing comma
            $trimmedNoComma = rtrim($trimmed, ',');

            if (preg_match('/^PRIMARY KEY\s/i', $trimmedNoComma)) {
                $indexes[] = "ADD {$trimmedNoComma}";
            } elseif (preg_match('/^UNIQUE KEY\s/i', $trimmedNoComma)) {
                $indexes[] = "ADD {$trimmedNoComma}";
            } elseif (preg_match('/^KEY\s/i', $trimmedNoComma)) {
                $indexes[] = "ADD {$trimmedNoComma}";
            } elseif (preg_match('/^CONSTRAINT\s/i', $trimmedNoComma)) {
                $constraints[] = "ADD {$trimmedNoComma}";
            } else {
                // Column definition — check for AUTO_INCREMENT
                if (stripos($trimmedNoComma, 'AUTO_INCREMENT') !== false) {
                    // Extract the column definition for the AUTO_INCREMENT section
                    // We need: column_name type ... NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=X
                    $colDef = $trimmedNoComma;
                    // Extract AUTO_INCREMENT value from the table options
                    if (preg_match('/AUTO_INCREMENT=(\d+)/i', $lastLine, $aiMatch)) {
                        $autoIncrementCol = $colDef . ', AUTO_INCREMENT=' . $aiMatch[1];
                    }
                    // For the clean CREATE TABLE, keep column but remove AUTO_INCREMENT keyword
                    // Actually phpMyAdmin keeps the column as-is without AUTO_INCREMENT in CREATE TABLE
                    // but we need to keep the NOT NULL
                    $cleanCol = preg_replace('/\s*AUTO_INCREMENT/i', '', $trimmedNoComma);
                    $cleanLines[] = '  ' . $cleanCol;
                } else {
                    $cleanLines[] = '  ' . $trimmedNoComma;
                }
            }
        }

        // Remove trailing comma from last column line
        $lastIdx = count($cleanLines) - 1;
        if ($lastIdx >= 0) {
            $cleanLines[$lastIdx] = rtrim($cleanLines[$lastIdx], ',');
        }

        // Build clean CREATE TABLE (without AUTO_INCREMENT in table options)
        $tableOptions = preg_replace('/\s*AUTO_INCREMENT=\d+/i', '', $lastLine);
        $cleanCreate = $firstLine . "\n" . implode(",\n", $cleanLines) . "\n" . $tableOptions;

        // Store indexes and constraints
        if (!empty($indexes)) {
            $allIndexes[$table] = $indexes;
        }
        if ($autoIncrementCol) {
            $allAutoIncrements[$table] = $autoIncrementCol;
        }
        if (!empty($constraints)) {
            $allConstraints[$table] = $constraints;
        }

        return $cleanCreate;
    }
}
