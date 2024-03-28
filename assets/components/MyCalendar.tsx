import { useState } from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

export default function MyCalendar() {
  const [selectedDate, setSelectedDate] = useState(null);

  const handleDateSelect = (selectInfo) => {
    setSelectedDate(selectInfo.startStr);
  };
  
  return (
    <div>
      <h1>Calendario</h1>
        {/* <FullCalendar 
          plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]},
          initialView={"dayGridMonth"},
          headerToolbar: [
            start: "title",
            center: "",
            end: "today prev,next",
          ]
        /> */}
        <FullCalendar
          plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
          initialView="dayGridMonth"
          selectable={true}
          events={[
            { title: 'Event 1', start: '2024-03-25T10:00:00', end: '2024-03-25T12:00:00' },
            { title: 'Event 2', start: '2024-03-26T14:00:00', end: '2024-03-26T16:00:00' },
            { title: 'Event 3', start: '2024-03-27T09:00:00', end: '2024-03-27T11:00:00' },
          ]}
          select={handleDateSelect}
        />
        {selectedDate && <p>Selected date: {selectedDate}</p>}
    </div>
  )
}