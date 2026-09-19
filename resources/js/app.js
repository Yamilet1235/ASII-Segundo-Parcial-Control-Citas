import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import esLocale from '@fullcalendar/core/locales/es';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';

const root = document.querySelector('[data-appointments-app]');

if (root) {
    const notice = document.querySelector('#notice');
    const dialog = document.querySelector('#appointment-dialog');
    const form = document.querySelector('#appointment-form');
    const detailEmpty = document.querySelector('#detail-empty');
    const detailContent = document.querySelector('#detail-content');
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

    const showDetail = (appointment) => {
        const format = new Intl.DateTimeFormat('es-MX', {
            dateStyle: 'medium',
            timeStyle: 'short',
        });

        detailEmpty.hidden = true;
        detailContent.hidden = false;
        document.querySelector('#detail-patient').textContent = appointment.patient.name;
        document.querySelector('#detail-doctor').textContent = appointment.doctor.name;
        document.querySelector('#detail-time').textContent = `${format.format(new Date(appointment.start_at))} - ${format.format(new Date(appointment.end_at))}`;
        document.querySelector('#detail-reason').textContent = appointment.reason;
        document.querySelector('#detail-status').value = appointment.status;
        detailContent.dataset.appointmentId = appointment.id;
    };

    const fillSelect = (selector, items) => {
        const select = document.querySelector(selector);
        select.replaceChildren(...items.map((item) => new Option(item.name, item.id)));
    };

    const openDialog = async () => {
        try {
            const [patients, doctors] = await Promise.all([
                request('get', '/api/pacientes'),
                request('get', '/api/doctores'),
            ]);
            fillSelect('#patient-select', patients);
            fillSelect('#doctor-select', doctors);
            dialog.showModal();
        } catch {
            // request() already shows the API error.
        }
    };

    const toDatabaseDate = (date) => {
        const pad = (value) => String(value).padStart(2, '0');
        return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
    };

    const saveSchedule = async ({ event, revert }) => {
        try {
            const appointment = await request('put', `/api/citas/${event.id}`, {
                start_at: toDatabaseDate(event.start),
                end_at: toDatabaseDate(event.end),
            });
            event.setExtendedProp('appointment', appointment);
            showDetail(appointment);
            showNotice('El horario fue actualizado correctamente.');
        } catch {
            revert();
        }
    };

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
        editable: true,
        eventClick: ({ event }) => showDetail(event.extendedProps.appointment),
        eventDrop: saveSchedule,
        eventResize: saveSchedule,
        events: async (_info, success, failure) => {
            try {
                success((await request('get', '/api/citas')).map(toEvent));
            } catch (error) {
                failure(error);
            }
        },
    });

    calendar.render();

    document.querySelector('#new-appointment').addEventListener('click', openDialog);
    document.querySelector('#close-dialog').addEventListener('click', () => dialog.close());
    document.querySelector('#cancel-dialog').addEventListener('click', () => dialog.close());
    document.querySelector('#save-status').addEventListener('click', async () => {
        const appointmentId = detailContent.dataset.appointmentId;

        try {
            const appointment = await request('patch', `/api/citas/${appointmentId}/estado`, {
                status: document.querySelector('#detail-status').value,
            });
            showDetail(appointment);
            calendar.refetchEvents();
            showNotice('El estado fue actualizado correctamente.');
        } catch {
            // request() keeps the previous state and shows the API error.
        }
    });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        try {
            await request('post', '/api/citas', Object.fromEntries(new FormData(form)));
            form.reset();
            dialog.close();
            calendar.refetchEvents();
            showNotice('La cita fue creada correctamente.');
        } catch {
            // Keep the form open so the user can correct it.
        }
    });
}
