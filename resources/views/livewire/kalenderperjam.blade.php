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
                    initialView: 'timeGridWeek',
                    events: @json($events),
                    eventClick: function(info) {
                        alert(
                            'Tanggal: ' + info.event.start.toLocaleDateString('id-ID') + '\n' +
                            'Waktu: ' + info.event.start.toLocaleTimeString('id-ID') + ' - ' +
                            info.event.end.toLocaleTimeString('id-ID') + '\n' +
                            'Tempat: ' + info.event.extendedProps.tempat + '\n' +
                            'Lokasi: ' + info.event.extendedProps.lokasi
                        );
                    },
                    eventContent: function(arg) {
                        return {
                            html: '<div class="fc-event-title text-xs font-medium" style="' +
                                'white-space: normal; ' +
                                'display: flex; ' +
                                'align-items: center; ' +
                                'justify-content: center;' +
                                'height: 100%; ' +
                                'width: 100%; ' +
                                'padding: 2px;' +
                                '">' +
                                arg.event.title +
                                '</div>'
                        };
                    },
                    locale: 'id',
                    headerToolbar: {
                        left: 'prev,next today',
                        center: 'title',
                        right: 'timeGridWeek'
                    },
                    buttonText: {
                        today: 'Hari Ini'
                    },
                    height: 500,
                    scrollTime: '07:00:00',
                    slotDuration: '01:00:00',
                    slotLabelFormat: {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: false
                    },
                    allDaySlot: false,
                });
                calendar.render();
            }
        }
    </script>
</div>
