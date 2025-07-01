document.addEventListener('DOMContentLoaded', function () {
    window.Echo.channel('orders')
        .listen('.order.created', (e) => {
            // إرسال حدث لتحديث الجدول
            window.Livewire.dispatch('refresh-table');
        });
});
