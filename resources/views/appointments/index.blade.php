<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Control de Citas Médicas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="appointments-page">
    <main class="app-shell" data-appointments-app>
        <header class="page-header">
            <div>
                <p class="eyebrow">Agenda clínica</p>
                <h1>Control de Citas Médicas</h1>
                <p class="subtitle">Consulta, programa y actualiza la agenda del equipo médico.</p>
            </div>
            <button class="button button-primary" id="new-appointment" type="button">Nueva cita</button>
        </header>

        <div id="notice" class="notice" role="status" hidden></div>

        <section class="workspace">
            <div class="calendar-card">
                <div class="legend" aria-label="Estados de citas">
                    <span><i class="status-pending"></i>Pendiente</span>
                    <span><i class="status-confirmed"></i>Confirmada</span>
                    <span><i class="status-cancelled"></i>Cancelada</span>
                    <span><i class="status-attended"></i>Atendida</span>
                </div>
                <div id="calendar"></div>
            </div>

            <aside class="detail-card" aria-live="polite">
                <div id="detail-empty">
                    <p class="eyebrow">Detalle</p>
                    <h2>Selecciona una cita</h2>
                    <p>Haz clic sobre un evento para consultar sus datos y cambiar su estado.</p>
                </div>
                <div id="detail-content" hidden>
                    <p class="eyebrow">Detalle de la cita</p>
                    <h2 id="detail-patient"></h2>
                    <dl>
                        <div><dt>Doctor</dt><dd id="detail-doctor"></dd></div>
                        <div><dt>Horario</dt><dd id="detail-time"></dd></div>
                        <div><dt>Motivo</dt><dd id="detail-reason"></dd></div>
                    </dl>
                    <label for="detail-status">Estado</label>
                    <select id="detail-status">
                        <option value="pendiente">Pendiente</option>
                        <option value="confirmada">Confirmada</option>
                        <option value="cancelada">Cancelada</option>
                        <option value="atendida">Atendida</option>
                    </select>
                    <button class="button button-primary full-width" id="save-status" type="button">Guardar estado</button>
                </div>
            </aside>
        </section>
    </main>

    <dialog id="appointment-dialog">
        <form id="appointment-form">
            <div class="dialog-heading">
                <div><p class="eyebrow">Nueva cita</p><h2>Programar atención</h2></div>
                <button class="icon-button" id="close-dialog" type="button" aria-label="Cerrar">&times;</button>
            </div>
            <label>Paciente<select name="patient_id" id="patient-select" required></select></label>
            <label>Doctor<select name="doctor_id" id="doctor-select" required></select></label>
            <div class="field-row">
                <label>Inicio<input type="datetime-local" name="start_at" required></label>
                <label>Fin<input type="datetime-local" name="end_at" required></label>
            </div>
            <label>Motivo<textarea name="reason" rows="3" maxlength="255" required></textarea></label>
            <div class="dialog-actions">
                <button class="button button-secondary" id="cancel-dialog" type="button">Cancelar</button>
                <button class="button button-primary" type="submit">Crear cita</button>
            </div>
        </form>
    </dialog>
</body>
</html>
