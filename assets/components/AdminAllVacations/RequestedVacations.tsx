import { useParams } from 'react-router-dom';
import { VacationType } from '../../services/types';
import { Button, Form, Message, Modal, Table } from 'semantic-ui-react';
import { useState } from 'react';
import vacationService from '../../services/vacation-service';
import errorProcessor from '../../services/errorProcessor';
import { formatDateTime } from '../utils/dateUtils';

interface Props {
    vacations: VacationType[];
    updateVacations: () => void;
}

/* eslint-disable-next-line */
const RequestedVacations: React.FC<Props> = ({ vacations, updateVacations }) => {
    const { id } = useParams<{ id: string }>();
    const [confirmModalOpen, setConfirmModalOpen] = useState<boolean>(false);
    const [rejectModalOpen, setRejectModalOpen] = useState<boolean>(false);
    /* eslint-disable-next-line */
    const [error, setError] = useState<string>('');
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});
    const [loading, setLoading] = useState<boolean>(false);
    const [vacationData, setVacationData] = useState<Partial<VacationType>>({
        id,
        dateFrom: '',
        dateTo: '',
        note: '',
        rejectionNote: ''
    });
        
    /* eslint-disable-next-line */
    if (!vacations || vacations.length === 0) {
        return <Message className='Vacation__Message'>There are no requested vacations yet.</Message>;
    }

    const handleConfirm = async (event: React.MouseEvent<HTMLButtonElement, MouseEvent>, id: string) => {
        event.preventDefault();
        setLoading(true);
        try {
            await vacationService.confirmVacation(id, vacationData);
            setConfirmModalOpen(false);
            updateVacations();
        } catch (error) {
            setError('Error' + (error as Error).message);
        } finally {
            setLoading(false);
        }
    };

    const handleReject = async (event: React.MouseEvent<HTMLButtonElement, MouseEvent>, id: string) => {
        event.preventDefault();
        setLoading(true);
        try {
            const fieldErrors: { [key: string]: string } = {};
            if (vacationData.rejectionNote.trim() === '') {
                fieldErrors['rejectionNote'] = 'Field should not be empty';
            }

            if (Object.keys(fieldErrors).length > 0) {
                setFormErrors(fieldErrors);
                return;
            }

            setFormErrors({});

            setFormErrors({});

            await vacationService.rejectVacation(id, vacationData);
            setRejectModalOpen(false);
            updateVacations();
        } catch (error) {
            errorProcessor(error, setError, setFormErrors);
        } finally {
            setLoading(false);
        }
    };

    const openConfirmModal = (vacation: VacationType) => {
        setVacationData({
            id: vacation.id,
            dateFrom: formatDateTime(vacation.dateFrom, true),
            dateTo: formatDateTime(vacation.dateTo, true),
            note: vacation.note
        });
        setConfirmModalOpen(true);
    };

    const openRejectModal = (vacation: VacationType) => {
        setVacationData({
            id: vacation.id,
            dateFrom: formatDateTime(vacation.dateFrom, true),
            dateTo: formatDateTime(vacation.dateTo, true),
            note: vacation.note,
            rejectionNote: vacation.rejectionNote
        });
        setRejectModalOpen(true);
    };

    const handleChangeRejectionNote = (e: React.ChangeEvent<HTMLTextAreaElement>) => {
        const { value } = e.target;
    
        if (value.trim() === '') {
            setFormErrors({ rejectionNote: 'Field should not be empty' });
        } else {
            setFormErrors({});
        }
    
        setVacationData({ ...vacationData, rejectionNote: value });
    };

    return (
        <div className="requested-vacation">
            <div className='Table_Container'>
                <Table celled inverted selectable striped>
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell>Requested by</Table.HeaderCell>
                            <Table.HeaderCell>Requested at</Table.HeaderCell>
                            <Table.HeaderCell>Start date</Table.HeaderCell>
                            <Table.HeaderCell>End date</Table.HeaderCell>
                            <Table.HeaderCell>Note</Table.HeaderCell>
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
                                <Table.Cell>
                                    <Button color="green" onClick={() => openConfirmModal(vacation)}>Confirm</Button>
                                    <Button color="red" onClick={() => openRejectModal(vacation)}>Reject</Button>
                                </Table.Cell>
                            </Table.Row>
                        ))}
                    </Table.Body>
                </Table>
            </div>
            <Modal open={confirmModalOpen} onClose={() => setConfirmModalOpen(false)} className='modal-wrapper'>
                <Modal.Header className='modal-header'>Confirm vacation</Modal.Header>
                <Modal.Content className='modal-content'>
                    <p>Are you sure you want to confirm this vacation request?</p>
                </Modal.Content>
                <Modal.Actions className='modal-actions'>
                    <Button onClick={() => setConfirmModalOpen(false)}>Cancel</Button>
                    <Button color='green' loading={loading} onClick={(e) => handleConfirm(e, vacationData.id)}>Confirm</Button>
                </Modal.Actions>
            </Modal>
            <Modal open={rejectModalOpen} onClose={() => setRejectModalOpen(false)} className='modal-wrapper'>
                <Modal.Header className='modal-header'>Reject vacation</Modal.Header>
                <Modal.Content className='modal-content'>
                    <p>Are you sure you want to reject this vacation request?</p>
                    <Form>
                        <Form.TextArea
                            label='Rejection Note'
                            placeholder='Enter rejection note here...'
                            value={vacationData.rejectionNote}
                            // onChange={(e) => setVacationData({ ...vacationData, rejectionNote: e.target.value })}
                            onChange={handleChangeRejectionNote}
                            error={formErrors['rejectionNote']}
                        />
                    </Form>
                </Modal.Content>
                <Modal.Actions className='modal-actions'>
                    <Button onClick={() => setRejectModalOpen(false)}>Cancel</Button>
                    <Button color='red' loading={loading} onClick={(e) => handleReject(e, vacationData.id)}>Reject</Button>
                </Modal.Actions>
            </Modal>
        </div>
    );
};

export default RequestedVacations;