import { useEffect, useRef, useState } from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import '../styles/my-calendar.scss';
import { VacationType, CalendarDays, ReservedDayType } from '../services/types';
import apiService from '../services/api-service';
import { Dimmer, Loader } from 'semantic-ui-react';

export default function MyCalendar() {
    // const [selectedDate, setSelectedDate] = useState<{ startDate: string | null; endDate: string | null }>({
    //     startDate: null,
    //     endDate: null
    // });

    const [selectedDate, setSelectedDate] = useState<{ startDate: string | null; endDate: string | null }>(() => {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        return {
          startDate: firstDayOfMonth.toISOString().split('T')[0],
          endDate: lastDayOfMonth.toISOString().split('T')[0]
        };
      });

    // TODO: gettina pradzioj sitas tuscias/null string reiksmes, turetu negettint.
    // const [calendarDays, setCalendarDays] = useState<{ startDate: string; endDate: string }>({
    //     // TODO: pagooglint kaip firstDay ir lastDay of month ir cia paduot string ne datetime, formatuot
    //     startDate: '',
    //     endDate: ''
    // });

    const [calendarDays, setCalendarDays] = useState<{ startDate: string; endDate: string }>(() => {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1, 0);
        return {
          startDate: firstDayOfMonth.toISOString().split('T')[0],
          endDate: lastDayOfMonth.toISOString().split('T')[0]
        };
      });

    const uniqueEventIds = new Set<string>();
    const [confirmedVacations, setConfirmedVacations] = useState<CalendarDays>({});
    const calendarRef = useRef<FullCalendar>();
    const [reservedDays, setReservedDays] = useState<ReservedDayType[]>([]);
    const [loading, setLoading] = useState<boolean>(false);


    useEffect(() => {
        const fetchConfirmedVacations = async () => {
            try {
                setLoading(true);
                const { startDate, endDate } = calendarDays;
                // ne api service, o repositorija ir nedet query params i route, pasidaryt, kad priima argumentus, array key startdate, kitas key enddate ir value enddate
                // api turi sumapint/subuildint querius
                const vacations = await apiService.get<CalendarDays>(`/vacations/`, {startDate, endDate});

                console.log(vacations);
                setConfirmedVacations(vacations);
            } catch (error) {
                // TODO: handle
                console.error('Error fetching confirmed vacations:', error);
            } finally {
                setLoading(false);
            }
        };

        const fetchReservedDays = async () => {
            try {
                setLoading(true);
                const { startDate, endDate } = calendarDays;
                const reserved = await apiService.get<ReservedDayType[]>(`/admin/reserved-day/?startDate=${startDate}&endDate=${endDate}`);

                console.log(reserved);
                setReservedDays(reserved);
            } catch (error) {
            // TODO: handle
                console.error('Error fetching reserved days:', error);
            } finally {
                setLoading(false);
            }
        };

        fetchConfirmedVacations();
        fetchReservedDays();

    }, [calendarDays]);

    const handleDatesSet = () => {
        const calendarApi = calendarRef.current?.getApi();
        if (calendarApi) {
            const startDate = calendarApi.view.activeStart.toISOString().split('T')[0];
            const endDate = calendarApi.view.activeEnd.toISOString().split('T')[0];
            setCalendarDays({ startDate, endDate });
        }
    };

    const handleDateSelect = (selectInfo: { startStr: string; endStr: string }) => {
        const { startStr: startDate, endStr: endDate } = selectInfo;

        setSelectedDate({ startDate, endDate });
    };


    const handleEventClick = (clickInfo: any) => {
        const event = clickInfo.event;

        const startDate = new Date(event.start).toISOString().replace(/T/, ' ').replace(/\..+/, '');
        const endDate = new Date(event.end).toISOString().replace(/T/, ' ').replace(/\..+/, '');
        const requestedAt = new Date(event.extendedProps.requestedAt).toISOString().replace(/T/, ' ').replace(/\..+/, '');

        alert(`Requested by: ${event.title}\nStart date: ${startDate}\nEnd date: ${endDate}\nRequested at: ${requestedAt}`);
    };
    
    const mapCalendarList = () => {
        const vacations = Object.keys(confirmedVacations).flatMap(date =>
            confirmedVacations[date].map((vacation: VacationType)  => {
                const eventId = vacation.id;
                if (uniqueEventIds.has(eventId)) {
                    return null;
                } else {
                    uniqueEventIds.add(eventId);
                    return {
                        title: `${vacation.requestedBy.firstName} ${vacation.requestedBy.lastName}`,
                        start: vacation.dateFrom,
                        end: vacation.dateTo,
                        requestedAt: vacation.requestedAt
                    };
                }
            })
        )
        .filter(event => event !== null);
        console.log('Vacations', vacations);
        return vacations;
    };

    const mapReservedDays = () => {
        const reservedEvents = reservedDays.map((reservedDay: ReservedDayType) => {
            const endDate = new Date(reservedDay.dateTo);
            endDate.setHours(23, 59, 59, 999);
    
            return {
                start: reservedDay.dateFrom,
                end: endDate.toISOString(),
                allDay: true,
                color: '#cb983a',
                display: 'background',
                classNames: ['reserved-day']
            };
        });
        console.log('Reserved Days', reservedEvents);
        return reservedEvents;
    };

    // if (loading) {
    //     return (
    //         <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
    //             <Loader>Loading</Loader>
    //         </Dimmer>
    //     );
    // }

    const combinedEvents = [];
    combinedEvents.push(...mapCalendarList());
    combinedEvents.push(...mapReservedDays());

    return (
    <div>
        <FullCalendar
            ref={calendarRef}
            plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
            initialView="dayGridMonth"
            selectable={true}
            // events={[...mapCalendarList(), ...mapReservedDays()]}
            events={combinedEvents}
            select={handleDateSelect}
            firstDay={1}
            datesSet={handleDatesSet}
            fixedWeekCount={false}
            timeZone="Europe/London"
            displayEventTime={false}
            eventClick={handleEventClick}
            dayMaxEventRows={true}
            dayMaxEvents={3}
            aspectRatio={1.75}
        />
        {selectedDate.startDate && selectedDate.endDate && (
            <p>
                Selected start date: {selectedDate.startDate} <br />
                Selected end date: {selectedDate.endDate}
            </p>
        )}
    </div>
    )
}