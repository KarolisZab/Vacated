import { useEffect, useRef, useState } from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import '../styles/my-calendar.scss';
import { VacationType, CalendarDays, ReservedDayType } from '../services/types';
import { Button, Form, Message, Modal } from 'semantic-ui-react';
import vacationService from '../services/vacation-service';
import reservedDayService from '../services/reserved-day-service';
import { useNavigate } from 'react-router-dom';

export default function MyCalendar() {
    const navigate = useNavigate();
    const [selectedDate, setSelectedDate] = useState<{ startDate: string | null; endDate: string | null }>(() => {
        return {
            startDate: null,
            endDate: null
        };
    });

    const [calendarDays, setCalendarDays] = useState<{ startDate: string; endDate: string }>(() => {
        const today = new Date();
        const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDayOfMonth = new Date(today.getFullYear(), today.getMonth() + 1);
        return {
            startDate: firstDayOfMonth.toISOString().split('T')[0],
            endDate: lastDayOfMonth.toISOString().split('T')[0]
        };
    });

    const uniqueEventIds = new Set<string>();
    const [confirmedVacations, setConfirmedVacations] = useState<CalendarDays>({});
    const calendarRef = useRef<FullCalendar>();
    const [reservedDays, setReservedDays] = useState<ReservedDayType[]>([]);
    // const [loading, setLoading] = useState<boolean>(false);
    const [note, setNote] = useState<string>('');
    const [showModal, setShowModal] = useState<boolean>(false);
    const [modalError, setModalError] = useState<string>('');
    /* eslint-disable-next-line */
    const [error, setError] = useState<string>('');

    useEffect(() => {
        const fetchData = async () => {
            try {
                // setLoading(true);
                
                const { startDate, endDate } = calendarDays;
                
                const [vacations, reserved] = await Promise.all([
                    vacationService.getConfirmedAndSelfRequestedVacations(startDate, endDate),
                    reservedDayService.getReservedDays(startDate, endDate)
                ]);
                
                setConfirmedVacations(vacations);
                setReservedDays(reserved);
            } catch (error) {
                navigate('/login')
            } finally {
                // setLoading(false);
            }
        };
    
        fetchData();
    }, [calendarDays]);

    // cia gal async await reik
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
        const adjustedEndDate = new Date(endDate);
        adjustedEndDate.setDate(adjustedEndDate.getDate() - 1);

        setSelectedDate({ startDate, endDate: adjustedEndDate.toISOString().split('T')[0] });
    };

    const handleRequestVacation = async () => {
        setShowModal(true);
    };

    const handleCloseModal = () => {
        setShowModal(false);
        setSelectedDate({ startDate: null, endDate: null });
        setModalError('');
    };    

    const handleConfirmVacationRequest = async () => {
        try {
            if (selectedDate.startDate && selectedDate.endDate) {
                setModalError('');
                await vacationService.requestVacation({ 
                    dateFrom: selectedDate.startDate,
                    dateTo: selectedDate.endDate,
                    note: note
                });

                const { startDate, endDate } = calendarDays;
                const updatedVacations = await vacationService.getConfirmedAndSelfRequestedVacations(startDate, endDate);
                setConfirmedVacations(updatedVacations);
                setCalendarDays(calendarDays);
                setSelectedDate({ startDate: null, endDate: null });
                setNote('');
                setShowModal(false);
            } else {
                setError('Start date and end date must be selected.');
            }
        } catch (error) {
            setModalError(error.response.data);
        }
    };

    /* eslint-disable-next-line */
    const handleEventClick = (clickInfo: any) => {
        const event = clickInfo.event;

        const startDate = new Date(event.start).toISOString()
            .replace(/T/, ' ')
            .replace(/\..+/, '');
        const endDate = new Date(event.end).toISOString()
            .replace(/T/, ' ')
            .replace(/\..+/, '');
        const requestedAt = new Date(event.extendedProps.requestedAt).toISOString()
            .replace(/T/, ' ')
            .replace(/\..+/, '');

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
        return reservedEvents;
    };

    // if (loading) {
    //     return (
    //         <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
    //             <Loader>Loading</Loader>
    //         </Dimmer>
    //     );
    // }

    return (
        <div>
            <div className="calendar-container">
                <Modal open={showModal} onClose={() => handleCloseModal()}>
                    <Modal.Header>Request vacation</Modal.Header>
                    <Modal.Content>
                        {modalError && <Message negative>{modalError}</Message>}
                        <p style={{ color: 'black' }}>
                            Selected start date: {selectedDate.startDate} <br />
                            Selected end date: {selectedDate.endDate}
                        </p>
                        <Form>
                            <Form.TextArea 
                                label='Note'
                                placeholder='Enter your note here'
                                value={note}
                                onChange={(e) => setNote(e.target.value)}
                            />
                        </Form>
                    </Modal.Content>
                    <Modal.Actions>
                        <Button color='black' onClick={() => handleCloseModal()}>
                            Cancel
                        </Button>
                        <Button
                            content="Request"
                            labelPosition='left'
                            icon='checkmark'
                            onClick={handleConfirmVacationRequest}
                            positive
                        />
                    </Modal.Actions>
                </Modal>

                <div className="request-button">
                    <Button color='teal' disabled={!selectedDate.startDate || !selectedDate.endDate} onClick={handleRequestVacation}>
                        Request vacation
                    </Button>
                </div>
            </div>

            <FullCalendar
                ref={calendarRef}
                plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin]}
                initialView="dayGridMonth"
                selectable={true}
                events={[...mapCalendarList(), ...mapReservedDays()]}
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
        </div>
    )
}