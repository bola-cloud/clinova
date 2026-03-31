<?php if (isset($component)) { $__componentOriginal08703cc13b04991a81cf42ec2219ae87 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal08703cc13b04991a81cf42ec2219ae87 = $attributes; } ?>
<?php $component = App\View\Components\ClinicLayout::resolve(['title' => ''.e(__('Global Patient Archive')).''] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('clinic-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\ClinicLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

    <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('shared.patients-list', []);

$__key = null;

$__key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-3203253933-0', $__key);

$__html = app('livewire')->mount($__name, $__params, $__key);

echo $__html;

unset($__html);
unset($__key);
unset($__name);
unset($__params);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal08703cc13b04991a81cf42ec2219ae87)): ?>
<?php $attributes = $__attributesOriginal08703cc13b04991a81cf42ec2219ae87; ?>
<?php unset($__attributesOriginal08703cc13b04991a81cf42ec2219ae87); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal08703cc13b04991a81cf42ec2219ae87)): ?>
<?php $component = $__componentOriginal08703cc13b04991a81cf42ec2219ae87; ?>
<?php unset($__componentOriginal08703cc13b04991a81cf42ec2219ae87); ?>
<?php endif; ?>
<?php /**PATH C:\Bola\Clinova\resources\views/dashboard/admin-patients.blade.php ENDPATH**/ ?>