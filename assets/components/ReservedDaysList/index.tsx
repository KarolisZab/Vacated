import {useState, useEffect} from 'react';
import reservedDayService from '../../services/reserved-day-service';
import { Link, useNavigate, useParams } from 'react-router-dom';
import { Button, Dimmer, Form, Loader, Message, Modal, Table } from 'semantic-ui-react';
import './styles.scss';
import { ReservedDayType } from '../../services/types';

const ReservedDaysList: React.FC = () => {
    const navigate = useNavigate();
    const [reservedDays, setReservedDays] = useState<ReservedDayType[]>([]);
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);
    const { id } = useParams<{ id: string }>();
    const [modalOpen, setModalOpen] = useState(false);
    const [deleteModalOpen, setDeleteModalOpen] = useState<boolean>(false);
    const [deleteId, setDeleteId] = useState<string>('');
    const [reservedDayData, setReservedDayData] = useState<Partial<ReservedDayType>>({
        id,
        dateFrom: '',
        dateTo: '',
        note: ''
    });
    const [newReservedDayModalOpen, setNewReservedDayModalOpen] = useState<boolean>(false);
    const [newReservedDayData, setNewReservedDayData] = useState<Partial<ReservedDayType>>({
        dateFrom: '',
        dateTo: '',
        note: ''
    });


    const formatDateTime = (dateTimeString: string, includeTime: boolean = false) => {
        const date = new Date(dateTimeString);
        if (includeTime) {
            return date
                .toISOString()
                .split('T')[0];
        } else {
            return date
            .toISOString()
            .replace('T', ' ')
            .replace(/\..+/, '');
        }
    };

    const fetchReservedDays = async () => {
        try {
            setLoading(true);

            const currentYear = new Date().getFullYear();
            const firstDayOfYear = new Date(`${currentYear}-01-01`);
            const lastDayOfYear = new Date(`${currentYear}-12-31`);

            const startDate = firstDayOfYear.toISOString().split('T')[0];
            const endDate = lastDayOfYear.toISOString().split('T')[0];

            const reservedDays = await reservedDayService.getReservedDays(startDate, endDate);
            setReservedDays(reservedDays);
        } catch (error) {
            setError('Error' + (error as Error).message);
            navigate("/");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchReservedDays();
    }, []);

    const closeModal = () => {
        setModalOpen(false);
        setDeleteModalOpen(false);
        setNewReservedDayModalOpen(false);
    };

    const handleUpdate = async (event: React.MouseEvent<HTMLButtonElement, MouseEvent>, id: string) => {
        event.preventDefault();
        console.log(reservedDayData);
        console.log(id);
        try {
            await reservedDayService.updateReservedDays(id, reservedDayData);
            closeModal();
            fetchReservedDays();
        } catch (error) {
            console.error('Error updating vacation:', error);
        }
    };

    const handleDelete = (id: string) => {
        setDeleteId(id);
        setDeleteModalOpen(true);
    };

    const confirmDelete = async () => {
        try {
            await reservedDayService.deleteReservedDay(deleteId);
            setReservedDays(prevReservedDays => prevReservedDays.filter(day => day.id !== id));
            closeModal();
        } catch (error) {
            setError('Error' + (error as Error).message);
            navigate("/");
        }
    };

    const handleNewReservedDaySubmit = async () => {
        try {
            await reservedDayService.reserveDays(newReservedDayData);
            closeModal();
            fetchReservedDays();
        } catch (error) {
            setError('Error' + (error as Error).message);
            navigate("/");
        }
    };

    return (
        <div className="reserved-days-list">
            <h1>Reserved days this year</h1>
            <Button color='teal' onClick={() => setNewReservedDayModalOpen(true)} className='reserve-button'>Reserve new days</Button>
            {error && <Message negative>{error}</Message>}
            <div className="loader-container">
                {loading && (
                    <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
                        <Loader>Loading</Loader>
                    </Dimmer>
                )}
                <div style={{ marginLeft: '2rem', marginRight: '2rem' }}>
                    <Table celled inverted selectable striped>
                        <Table.Header>
                            <Table.Row>
                                <Table.HeaderCell>Reserved by</Table.HeaderCell>
                                <Table.HeaderCell>Start date</Table.HeaderCell>
                                <Table.HeaderCell>End date</Table.HeaderCell>
                                <Table.HeaderCell>Note</Table.HeaderCell>
                                <Table.HeaderCell>Tags</Table.HeaderCell>
                                <Table.HeaderCell>Actions</Table.HeaderCell>
                            </Table.Row>
                        </Table.Header>

                        <Table.Body>
                            {reservedDays.map((reservedDay) => (
                                <Table.Row key={reservedDay.id}>
                                    <Table.Cell>{reservedDay.reservedBy.firstName} {reservedDay.reservedBy.lastName}</Table.Cell>
                                    <Table.Cell>{formatDateTime(reservedDay.dateFrom, true)}</Table.Cell>
                                    <Table.Cell>{formatDateTime(reservedDay.dateTo, true)}</Table.Cell>
                                    <Table.Cell>{reservedDay.note}</Table.Cell>
                                    <Table.Cell>{}</Table.Cell>
                                    <Table.Cell>
                                        <Button color="blue" onClick={() => {
                                            setReservedDayData({
                                                id: reservedDay.id,
                                                dateFrom: formatDateTime(reservedDay.dateFrom, true),
                                                dateTo: formatDateTime(reservedDay.dateTo, true),
                                                note: reservedDay.note
                                            });
                                            setModalOpen(true);
                                        }}>Update</Button>
                                        <Button negative onClick={() => handleDelete(reservedDay.id)}>Delete</Button>
                                    </Table.Cell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                </div>
                <Modal open={modalOpen} onClose={closeModal}>
                    <Modal.Header>Update Vacation</Modal.Header>
                        <Modal.Content>
                            <Form>
                                <Form.Input
                                    label='Start date'
                                    type='date'
                                    value={reservedDayData.dateFrom}
                                    onChange={(e) => setReservedDayData({ ...reservedDayData, dateFrom: e.target.value })}
                                />
                                <Form.Input
                                    label='End date'
                                    type='date'
                                    value={reservedDayData.dateTo}
                                    onChange={(e) => setReservedDayData({ ...reservedDayData, dateTo: e.target.value })}
                                />
                                <Form.TextArea
                                    label='Note'
                                    placeholder='Enter your note here'
                                    value={reservedDayData.note}
                                    onChange={(e) => setReservedDayData({ ...reservedDayData, note: e.target.value })}
                                />
                            </Form>
                        </Modal.Content>
                        <Modal.Actions>
                            <Button color='black' onClick={closeModal}>Cancel</Button>
                            <Button
                                content="Update"
                                labelPosition='left'
                                icon='checkmark'
                                onClick={(e) => handleUpdate(e, reservedDayData.id)}
                                positive
                            />
                        </Modal.Actions>
                </Modal>
                <Modal open={deleteModalOpen} onClose={closeModal}>
                        <Modal.Header>Delete Reservation</Modal.Header>
                        <Modal.Content>
                            <p style={{ color: 'black' }}>Are you sure you want to delete this reservation?</p>
                        </Modal.Content>
                        <Modal.Actions>
                            <Button color='black' onClick={closeModal}>Cancel</Button>
                            <Button
                                content="Delete"
                                labelPosition='left'
                                icon='trash'
                                onClick={confirmDelete}
                                negative
                            />
                        </Modal.Actions>
                </Modal>
                <Modal open={newReservedDayModalOpen} onClose={closeModal}>
                    <Modal.Header>New Reserved Day</Modal.Header>
                    <Modal.Content>
                        <Form>
                            <Form.Input
                                label='Start date'
                                type='date'
                                value={newReservedDayData.dateFrom}
                                onChange={(e) => setNewReservedDayData({ ...newReservedDayData, dateFrom: e.target.value })}
                            />
                            <Form.Input
                                label='End date'
                                type='date'
                                value={newReservedDayData.dateTo}
                                onChange={(e) => setNewReservedDayData({ ...newReservedDayData, dateTo: e.target.value })}
                            />
                            <Form.TextArea
                                label='Note'
                                placeholder='Enter your note here'
                                value={newReservedDayData.note}
                                onChange={(e) => setNewReservedDayData({ ...newReservedDayData, note: e.target.value })}
                            />
                        </Form>
                    </Modal.Content>
                    <Modal.Actions>
                        <Button color='black' onClick={closeModal}>Cancel</Button>
                        <Button
                            content="Create"
                            labelPosition='left'
                            icon='checkmark'
                            onClick={handleNewReservedDaySubmit}
                            positive
                        />
                    </Modal.Actions>
                </Modal>
            </div>
        </div>
    );
};

export default ReservedDaysList;