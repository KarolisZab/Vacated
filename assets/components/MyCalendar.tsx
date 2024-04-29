import { useEffect, useRef, useState } from 'react';
import FullCalendar from '@fullcalendar/react';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import multiMonthPlugin from '@fullcalendar/multimonth';
import '../styles/my-calendar.scss';
import { VacationType, CalendarDays, ReservedDayType, EmployeeType } from '../services/types';
import { Button, Dimmer, Form, Loader, Message, Modal, ModalActions, Popup } from 'semantic-ui-react';
import vacationService from '../services/vacation-service';
import reservedDayService from '../services/reserved-day-service';
import { useNavigate } from 'react-router-dom';
import employeeService from '../services/employee-service';

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
    const [loading, setLoading] = useState<boolean>(true);
    const [note, setNote] = useState<string>('');
    const [showModal, setShowModal] = useState<boolean>(false);
    const [modalError, setModalError] = useState<string>('');
    /* eslint-disable-next-line */
    const [error, setError] = useState<string>('');
    const [availableDays, setAvailableDays] = useState<number>(0);
    const [popupContent, setPopupContent] = useState<string>('');
    const [currentUser, setCurrentUser] = useState<EmployeeType | null>(null);

    useEffect(() => {
        const fetchData = async () => {
            try {                
                const { startDate, endDate } = calendarDays;
                
                const [vacations, reserved, currentUser] = await Promise.all([
                    vacationService.getConfirmedAndSelfRequestedVacations(startDate, endDate),
                    reservedDayService.getReservedDays(startDate, endDate),
                    employeeService.getCurrentUser()
                ]);
                
                setConfirmedVacations(vacations);
                setReservedDays(reserved);
                setCurrentUser(currentUser);
            } catch (error) {
                navigate('/login')
            } finally {
                setLoading(false);
            }
        };
    
        fetchData();
        fetchAvailableDays();
    }, [calendarDays]);

    const fetchAvailableDays = async () => {
        try {
            const result = await employeeService.getEmployeesAvailableVacationDays();
            setAvailableDays(result);
        } catch (error) {
            setError('Error: ' + (error as Error).message);
        }
    };

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
        setNote('');
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

                await fetchAvailableDays();

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
            .split('T')[0]
        const endDate = new Date(event.end).toISOString()
            .split('T')[0]
        const requestedAt = new Date(event.extendedProps.requestedAt).toISOString()
            .replace(/T/, ' ')
            .replace(/:\d+\.\d+Z/, '');
        const reviewedAt = new Date(event.extendedProps.reviewedAt).toISOString()
            .replace(/T/, ' ')
            .replace(/:\d+\.\d+Z/, '');
        
        if (event.extendedProps.confirmed === true) {
            setPopupContent(`
                <p style="color: black;"><strong>${event.title}</strong></p>
                <p style="color: black;">Requested at <strong>${requestedAt}</strong></p>
                <p style="color: black;">Starts at <strong>${startDate}</strong></p>
                <p style="color: black;">Ends at <strong>${endDate}</strong></p>
                <br>
                <p style="color: black;">Confirmed by <strong>${event.extendedProps.reviewedBy}</strong> at <strong>${reviewedAt}</strong></p>
            `);

        } else {
            setPopupContent(`
                <p style="color: black;"><strong>${event.title.replace('Requested: ', '')}</strong></p>
                <p style="color: black;">Requested at <strong>${requestedAt}</strong></p>
                <p style="color: black;">Starts at <strong>${startDate}</strong></p>
                <p style="color: black;">Ends at <strong>${endDate}</strong></p>
            `);
        }
    };

    const mapCalendarList = () => {
        const vacations = Object.keys(confirmedVacations).flatMap(date =>
            confirmedVacations[date].map((vacation: VacationType)  => {
                const eventId = vacation.id;
                if (uniqueEventIds.has(eventId)) {
                    return null;
                } else {
                    uniqueEventIds.add(eventId);
                    const styles = vacation.confirmed ? 'Calendar__VacationDay--confirmed' : 'Calendar__VacationDay--unconfirmed';
                    let title = `${vacation.requestedBy.firstName} ${vacation.requestedBy.lastName}`;
                    if (!vacation.confirmed) {
                        title = `Requested: ${title}`;
                    }
                    return {
                        title: title,
                        start: vacation.dateFrom,
                        end: vacation.dateTo,
                        requestedAt: vacation.requestedAt,
                        reviewedBy: vacation.reviewedBy ? `${vacation.reviewedBy.firstName} ${vacation.reviewedBy.lastName}` : '',
                        reviewedAt: vacation.reviewedAt,
                        confirmed: vacation.confirmed,
                        classNames: [styles, 'Calendar__VacationDay']
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
                display: 'background'
            };
        });
        return reservedEvents;
    };

    const mapTagsList = () => {
        const tagEvents = reservedDays.flatMap((reservedDay: ReservedDayType) => {
            const endDate = new Date(reservedDay.dateTo);
            endDate.setHours(23, 59, 59, 999);

            // return reservedDay.tags.map((tag) => {
            //     return {
            //         start: reservedDay.dateFrom,
            //         end: endDate.toISOString(),
            //         title: tag.name,
            //         color: tag.colorCode,
            //         classNames: ['tag-event'],
            //         display: 'list-item'
            //     };
            // });
            const eventTitle = reservedDay.tags.reduce((label, tag) => {
                if (label === '') {
                    return tag.name;
                }

                return `${label}, ${tag.name}`;
            }, '');
            return {
                start: reservedDay.dateFrom,
                end: endDate.toISOString(),
                title: eventTitle,
                color: reservedDay.tags[0].colorCode,
                classNames: ['tag-event'],
                display: 'list-item'
            };
        });
        return tagEvents;
    };

    if (loading) {
        return (
            <div className='loader-container'>
                <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
                    <Loader>Loading</Loader>
                </Dimmer>
            </div>
        );
    }

    return (
        <div>
            <div className="calendar-container">
                <Modal open={showModal} onClose={() => handleCloseModal()}>
                    <Modal.Header>Request vacation</Modal.Header>
                    <Modal.Content>
                        {modalError && <Message negative>{modalError}</Message>}
                        <Form>
                            <Form.Field>
                                <label>Selected Start Date:</label>
                                <Form.Input
                                    name="startDate"
                                    type='date'
                                    value={selectedDate.startDate}
                                    onChange={(e, { value }) => setSelectedDate(prevState => ({ ...prevState, startDate: value }))}
                                />
                            </Form.Field>
                            <Form.Field>
                                <label>Selected End Date:</label>
                                <Form.Input
                                    name="endDate"
                                    type='date'
                                    value={selectedDate.endDate}
                                    onChange={(e, { value }) => setSelectedDate(prevState => ({ ...prevState, endDate: value }))}
                                />
                            </Form.Field>
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
                <Modal size='mini' open={!!popupContent} onClose={() => setPopupContent('')}>
                    <Modal.Content>
                        <div style={{ color: 'black' }} dangerouslySetInnerHTML={{ __html: popupContent }}/>
                    </Modal.Content>
                    <Modal.Actions>
                        <Button onClick={() => setPopupContent('')}>Close</Button>
                    </Modal.Actions>
                </Modal>
                <div>
                    {currentUser && (
                        <div>
                            <p className='greeting-message'>
                                Hi, {currentUser.firstName} {currentUser.lastName}!
                            </p>
                            <p className='available-days-message'>
                                You have {availableDays} out of 20 available vacation days.
                            </p>
                            <Button color='teal' disabled={!selectedDate.startDate || !selectedDate.endDate} onClick={handleRequestVacation}>
                                Request vacation
                            </Button>
                        </div>
                    )}
                </div>
            </div>

            <FullCalendar
                ref={calendarRef}
                plugins={[dayGridPlugin, timeGridPlugin, interactionPlugin, multiMonthPlugin]}
                initialView="dayGridMonth"
                selectable={true}
                events={[...mapCalendarList(), ...mapReservedDays(), ...mapTagsList()]}
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
                headerToolbar={{
                    start: 'prev,next today',
                    center: 'title',
                    end: 'dayGridMonth,multiMonthYear'
                }}
            />
        </div>
    )
}