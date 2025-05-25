<div>
    <div id='calendar' class="mb-4"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            buatKalender();
        });

        // Buat ulang kalender ketika komponen Livewire diperbarui
        document.addEventListener('livewire:update', function() {
            buatKalender();
        });

        function buatKalender() {
            var calendarEl = document.getElementById('calendar');
            if (calendarEl) {
                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: @json($events),
                    eventClick: function(info) {
                        alert(
                            'Tanggal: ' + info.event.start.toLocaleDateString('id-ID') + '\n' +
                            'Tempat: ' + info.event.extendedProps.tempat + '\n' +
                            'Lokasi: ' + info.event.extendedProps.lokasi
                        );
                    },
                    eventContent: function(arg) {
                        return {
                            html: '<div class="fc-event-title text-xs font-medium" style="' +
                                'white-space: normal; ' +
                                'display: flex; ' +
                                'justify-content: center;' +
                                '">' +
                                arg.event.title +
                                '</div>'
                        };
                    },
                    locale: 'id',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'dayGridMonth'
                    },
                    buttonText: {
                        today: 'Hari Ini'
                    },
                    height: 'auto',
                    dayMaxEvents: true
                });
                calendar.render();
            }
        }
    </script>
</div>
