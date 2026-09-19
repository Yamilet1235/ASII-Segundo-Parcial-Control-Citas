import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import esLocale from '@fullcalendar/core/locales/es';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';

const root = document.querySelector('[data-appointments-app]');

if (root) {
    const notice = document.querySelector('#notice');
    const colors = {
        pendiente: '#d99116',
        confirmada: '#21866f',
        cancelada: '#89938f',
        atendida: '#6b5bb5',
    };

    const showNotice = (message, isError = false) => {
        notice.textContent = message;
        notice.classList.toggle('error', isError);
        notice.hidden = false;
        window.setTimeout(() => { notice.hidden = true; }, 5000);
    };

    const request = async (method, url, data) => {
        try {
            return (await window.axios({ method, url, data })).data;
        } catch (error) {
            const response = error.response;
            const validation = response?.data?.errors;
            const message = response?.data?.message
                || (validation && Object.values(validation).flat()[0])
                || 'No fue posible completar la solicitud.';

            showNotice(response?.status === 409
                ? 'El doctor ya tiene una cita en ese horario.'
                : message, true);
            throw error;
        }
    };

    const toEvent = (appointment) => ({
        id: String(appointment.id),
        title: `${appointment.doctor.name} · ${appointment.patient.name}`,
        start: appointment.start_at,
        end: appointment.end_at,
        backgroundColor: colors[appointment.status],
        borderColor: colors[appointment.status],
        extendedProps: { appointment },
    });

    const calendar = new Calendar(document.querySelector('#calendar'), {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locale: esLocale,
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek',
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana' },
        nowIndicator: true,
        height: 'auto',
        events: async (_info, success, failure) => {
            try {
                success((await request('get', '/api/citas')).map(toEvent));
            } catch (error) {
                failure(error);
            }
        },
    });

    calendar.render();
}
