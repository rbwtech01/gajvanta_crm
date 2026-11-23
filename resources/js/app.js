import './bootstrap';

import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';

// import '@fullcalendar/core/index.css';
// import '@fullcalendar/daygrid/index.css';

window.FullCalendar = {
    Calendar,
    dayGridPlugin,
    interactionPlugin,
};