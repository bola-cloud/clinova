<x-clinic-layout title="الاشتراك منتهي">
    <div class="max-w-md mx-auto mt-20 text-center">
        <div class="w-20 h-20 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
        </div>
        <h1 class="text-2xl font-bold mb-2">عذراً، اشتراكك غير مفعل</h1>
        <p class="text-gray-600 mb-8">يرجى التواصل مع الإدارة لتفعيل اشتراكك ومتابعة العمل.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="px-6 py-2 bg-nelly text-white rounded-xl font-medium">تسجيل الخروج</button>
        </form>
    </div>
</x-clinic-layout>
