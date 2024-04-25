import { useParams } from 'react-router-dom';
import { VacationType } from '../../services/types';
import { Button, Form, Message, Modal, Table } from 'semantic-ui-react';
import { useState } from 'react';
import vacationService from '../../services/vacation-service';
import { formatDateTime } from '../utils/dateUtils';

interface Props {
    vacations: VacationType[];
    updateVacations: () => void;

}

/* eslint-disable-next-line */
const ConfirmedVacations: React.FC<Props> = ({ vacations, updateVacations }) => {
    const { id } = useParams<{ id: string }>();
    const [modalOpen, setModalOpen] = useState(false);
    const [error, setError] = useState<string>('');
    const [vacationData, setVacationData] = useState<Partial<VacationType>>({
        id,
        dateFrom: '',
        dateTo: '',
        note: ''
    });
    
    /* eslint-disable-next-line */
    if (!vacations || vacations.length === 0) {
        return <Message>There are no confirmed vacations yet.</Message>;
    }

    const handleUpdate = async (event: React.MouseEvent<HTMLButtonElement, MouseEvent>, id: string) => {
        event.preventDefault();
        try {
            await vacationService.updateRequestedVacation(id, vacationData);
            closeModal();
            updateVacations();
        } catch (error) {
            setError('Error' + (error as Error).message);
        }
    };

    const closeModal = () => {
        setModalOpen(false);
    };

    return (
        <div className="requested-vacation">
            <div style={{ marginRight: '2rem' }}>
                {error && <Message negative>{error}</Message>}
                <Table celled inverted selectable striped>
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell>Requested by</Table.HeaderCell>
                            <Table.HeaderCell>Requested at</Table.HeaderCell>
                            <Table.HeaderCell>Start date</Table.HeaderCell>
                            <Table.HeaderCell>End date</Table.HeaderCell>
                            <Table.HeaderCell>Note</Table.HeaderCell>
                            <Table.HeaderCell>Reviewed by</Table.HeaderCell>
                            <Table.HeaderCell>Reviewed at</Table.HeaderCell>
                            <Table.HeaderCell>Actions</Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>

                    <Table.Body>
                        {/* eslint-disable-next-line */}
                        {vacations.map((vacation) => (
                            <Table.Row key={vacation.id}>
                                <Table.Cell>{`${vacation.requestedBy.firstName} ${vacation.requestedBy.lastName}`}</Table.Cell>
                                <Table.Cell>{formatDateTime(vacation.requestedAt)}</Table.Cell>
                                <Table.Cell>{formatDateTime(vacation.dateFrom, true)}</Table.Cell>
                                <Table.Cell>{formatDateTime(vacation.dateTo, true)}</Table.Cell>
                                <Table.Cell>{vacation.note}</Table.Cell>
                                <Table.Cell>{`${vacation.reviewedBy.firstName} ${vacation.reviewedBy.lastName}`}</Table.Cell>
                                <Table.Cell>{formatDateTime(vacation.reviewedAt)}</Table.Cell>
                                <Table.Cell>
                                    <Button color="blue" onClick={() => {
                                        setVacationData({
                                            id: vacation.id,
                                            dateFrom: formatDateTime(vacation.dateFrom, true),
                                            dateTo: formatDateTime(vacation.dateTo, true),
                                            note: vacation.note
                                        });
                                        setModalOpen(true);
                                    }}>Update</Button>
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
                            value={vacationData.dateFrom}
                            onChange={(e) => setVacationData({ ...vacationData, dateFrom: e.target.value })}
                        />
                        <Form.Input
                            label='End date'
                            type='date'
                            value={vacationData.dateTo}
                            onChange={(e) => setVacationData({ ...vacationData, dateTo: e.target.value })}
                        />
                        <Form.TextArea
                            label='Note'
                            placeholder='Enter your note here'
                            value={vacationData.note}
                            onChange={(e) => setVacationData({ ...vacationData, note: e.target.value })}
                        />
                    </Form>
                </Modal.Content>
                <Modal.Actions>
                    <Button color='black' onClick={closeModal}>Cancel</Button>
                    <Button
                        content="Update"
                        labelPosition='left'
                        icon='checkmark'
                        onClick={(e) => handleUpdate(e, vacationData.id)}
                        positive
                    />
                </Modal.Actions>
            </Modal>
        </div>
    );
};

export default ConfirmedVacations;