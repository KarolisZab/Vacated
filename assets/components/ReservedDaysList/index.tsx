import {useState, useEffect} from 'react';
import reservedDayService from '../../services/reserved-day-service';
import { useNavigate, useParams } from 'react-router-dom';
import { Button, Dimmer, Dropdown, DropdownProps, Form, Label, ListItem, Loader, Message, Modal, Pagination, Table } from 'semantic-ui-react';
import './styles.scss';
import { ReservedDayType, TagType } from '../../services/types';
import tagService from '../../services/tag-service';
import errorProcessor from '../../services/errorProcessor';

const ReservedDaysList: React.FC = () => {
    const navigate = useNavigate();
    const [reservedDays, setReservedDays] = useState<ReservedDayType[]>([]);
    /* eslint-disable-next-line */
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);
    const { id } = useParams<{ id: string }>();
    const [modalOpen, setModalOpen] = useState(false);
    const [deleteModalOpen, setDeleteModalOpen] = useState<boolean>(false);
    const [deleteId, setDeleteId] = useState<string>('');
    const [tags, setTags] = useState<TagType[]>([]);
    const [reservedDayData, setReservedDayData] = useState<Partial<ReservedDayType>>({
        id,
        dateFrom: '',
        dateTo: '',
        note: '',
        tags: []
    });
    const [newReservedDayModalOpen, setNewReservedDayModalOpen] = useState<boolean>(false);
    const [newReservedDayData, setNewReservedDayData] = useState<Partial<ReservedDayType>>({
        dateFrom: '',
        dateTo: '',
        note: '',
        tags: []
    });
    const [page, setPage] = useState<number>(1);
    const [totalItems, setTotalItems] = useState<number>(0);
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});
    const [modalError, setModalError] = useState<string>('');

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

            const results = await reservedDayService.getReservedDaysList(startDate, endDate, page);
            setReservedDays(results.items);
            setTotalItems(results.totalItems);
        } catch (error) {
            setError('Error' + (error as Error).message);
            // navigate("/");
        } finally {
            setLoading(false);
        }
    };

    const fetchTags = async () => {
        try {
            const tags = await tagService.getAllTags();
            setTags(tags);
        } catch (error) {
            setError('Error: ' + (error as Error).message);
        }
    };

    useEffect(() => {
        fetchReservedDays();
        fetchTags();
    }, [page]);

    useEffect(() => {
        if (!newReservedDayModalOpen) {
            setNewReservedDayData({
                dateFrom: '',
                dateTo: '',
                note: '',
                tags: []
            });
            setFormErrors({});
        }
    }, [newReservedDayModalOpen]);

    const handleTagsChange = (e: React.SyntheticEvent<HTMLElement, Event>, { value }: DropdownProps) => {
        if (Array.isArray(value)) {
            const selectedTags: TagType[] = value.map(tagName => {
                const tag = tags.find(tag => tag.name === tagName);
                if (tag) {
                    return tag;
                } else {
                    return { id: '', name: '', colorCode: '' };
                }
            });
            setNewReservedDayData({ ...newReservedDayData, tags: selectedTags });
        }
    };

    const closeModal = () => {
        setModalOpen(false);
        setDeleteModalOpen(false);
        setNewReservedDayModalOpen(false);
        setModalError('');
    };

    const handleUpdate = async (event: React.MouseEvent<HTMLButtonElement, MouseEvent>, id: string) => {
        event.preventDefault();
        try {
            if (reservedDayData.dateFrom.trim() === '') {
                setFormErrors({ dateFrom: 'Start date should not be empty' });
                return;
            }

            if (reservedDayData.dateTo.trim() === '') {
                setFormErrors({ dateTo: 'End date should not be empty' });
                return;
            }

            if (reservedDayData.note.trim() === '') {
                setFormErrors({ note: 'Note should not be empty' });
                return;
            }

            setFormErrors({});

            await reservedDayService.updateReservedDays(id, reservedDayData);
            closeModal();
            fetchReservedDays();
        } catch (error) {
            errorProcessor(error, setError, setFormErrors);
            setModalError(error.response.data);
        }
    };

    const handleUpdateTagsChange = (e: React.SyntheticEvent<HTMLElement, Event>, { value }: DropdownProps) => {
        if (Array.isArray(value)) {
            const selectedTags: TagType[] = value.map(tagName => {
                const tag = tags.find(tag => tag.name === tagName);
                if (tag) {
                    return tag;
                } else {
                    return { id: '', name: '', colorCode: '' };
                }
            });
            setReservedDayData({ ...reservedDayData, tags: selectedTags });
        }
    };

    const handleTagCreate = async (e: React.KeyboardEvent<HTMLElement>, { value }: DropdownProps) => {
        if (e.key === 'Enter' && value) {
            try {
                const newTag: TagType = { id: '', name: value as string, colorCode: 'grey' };
                setTags([...tags, newTag]);

                if (modalOpen) {
                    setReservedDayData({ ...reservedDayData, tags: [...reservedDayData.tags, newTag] });
                } else if (newReservedDayModalOpen) {
                    setNewReservedDayData({ ...newReservedDayData, tags: [...newReservedDayData.tags, newTag] });
                }
            } catch (error) {
                setError('Error: ' + (error as Error).message);
            }
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
            navigate(-1);
        }
    };

    const handleNewReservedDaySubmit = async () => {
        try {
            if (newReservedDayData.dateFrom.trim() === '') {
                setFormErrors({ dateFrom: 'Start date should not be empty' });
                return;
            }

            if (newReservedDayData.dateTo.trim() === '') {
                setFormErrors({ dateTo: 'End date should not be empty' });
                return;
            }

            if (newReservedDayData.note.trim() === '') {
                setFormErrors({ note: 'Note should not be empty' });
                return;
            }

            setFormErrors({});

            await reservedDayService.reserveDays(newReservedDayData);
            closeModal();
            fetchReservedDays();
        } catch (error) {
            errorProcessor(error, setError, setFormErrors);
            setModalError(error.response.data);
            // navigate("/");
        }
    };

    /* eslint-disable-next-line */
    const handlePaginationChange = (event: React.MouseEvent, data: any) => {
        setPage(data.activePage);
    };

    return (
        <div className="reserved-days-list">
            <h1>Reserved days</h1>
            <Button color='teal' onClick={() => setNewReservedDayModalOpen(true)} className='reserve-button'>Reserve days</Button>
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
                                    <Table.Cell>
                                        {reservedDay.tags.map((tag) => (
                                            <ListItem key={tag.id}>
                                                <Label style={{ backgroundColor: tag.colorCode }} horizontal>
                                                    {tag.name}
                                                </Label>
                                            </ListItem>
                                        ))}
                                    </Table.Cell>
                                    <Table.Cell>
                                        <Button color="blue" onClick={() => {
                                            const formattedTags = reservedDay.tags.map(tag => ({
                                                id: tag.id,
                                                name: tag.name,
                                                colorCode: tag.colorCode
                                            }));
                                            
                                            setReservedDayData({
                                                id: reservedDay.id,
                                                dateFrom: formatDateTime(reservedDay.dateFrom, true),
                                                dateTo: formatDateTime(reservedDay.dateTo, true),
                                                note: reservedDay.note,
                                                tags: formattedTags
                                            });
                                            setModalOpen(true);
                                        }}>Update</Button>
                                        <Button negative onClick={() => handleDelete(reservedDay.id)}>Delete</Button>
                                    </Table.Cell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                    <Pagination
                        totalPages={Math.ceil(totalItems / 10)}
                        activePage={page}
                        onPageChange={handlePaginationChange}
                        size="mini"
                    />
                </div>
                <Modal open={modalOpen} onClose={closeModal}>
                    <Modal.Header>Update reserved days</Modal.Header>
                    <Modal.Content>
                        {modalError && <Message negative>{modalError}</Message>}
                        <Form>
                            <Form.Input
                                label='Start date'
                                type='date'
                                value={reservedDayData.dateFrom}
                                onChange={(e) => setReservedDayData({ ...reservedDayData, dateFrom: e.target.value })}
                                error={formErrors['dateFrom']}
                            />
                            <Form.Input
                                label='End date'
                                type='date'
                                value={reservedDayData.dateTo}
                                onChange={(e) => setReservedDayData({ ...reservedDayData, dateTo: e.target.value })}
                                error={formErrors['dateTo']}
                            />
                            <Form.TextArea
                                label='Note'
                                placeholder='Enter your note here'
                                value={reservedDayData.note}
                                onChange={(e) => setReservedDayData({ ...reservedDayData, note: e.target.value })}
                                error={formErrors['note']}
                            />
                            <Form.Field>
                                <Dropdown
                                    placeholder="Select tags"
                                    fluid
                                    multiple
                                    search
                                    selection
                                    options={tags.map(tag => ({ key: tag.id, text: tag.name, value: tag.name }))}
                                    onChange={handleUpdateTagsChange}
                                    value={reservedDayData.tags.map(tag => tag.name)}
                                    allowAdditions
                                    onAddItem={handleTagCreate}
                                />
                            </Form.Field>
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
                        {modalError && <Message negative>{modalError}</Message>}
                        <Form>
                            <Form.Input
                                label='Start date'
                                type='date'
                                name='dateFrom'
                                value={newReservedDayData.dateFrom}
                                onChange={(e) => setNewReservedDayData({ ...newReservedDayData, dateFrom: e.target.value })}
                                error={formErrors['dateFrom']}
                            />
                            <Form.Input
                                label='End date'
                                type='date'
                                name='dateTo'
                                value={newReservedDayData.dateTo}
                                onChange={(e) => setNewReservedDayData({ ...newReservedDayData, dateTo: e.target.value })}
                                error={formErrors['dateTo']}
                            />
                            <Form.TextArea
                                label='Note'
                                name='note'
                                placeholder='Enter your note here'
                                value={newReservedDayData.note}
                                onChange={(e) => setNewReservedDayData({ ...newReservedDayData, note: e.target.value })}
                                error={formErrors['note']}
                            />
                            <Form.Field>
                                <Dropdown
                                    placeholder="Select tags"
                                    fluid
                                    multiple
                                    search
                                    selection
                                    options={tags.map(tag => ({ key: tag.id, text: tag.name, value: tag.name }))}
                                    onChange={handleTagsChange}
                                    value={newReservedDayData.tags.map(tag => tag.name)}
                                    allowAdditions
                                    onAddItem={handleTagCreate}
                                />
                            </Form.Field>
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