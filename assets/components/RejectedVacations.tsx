import { VacationType } from '../services/types';
import { Message, Table } from 'semantic-ui-react';
import { formatDateTime } from './utils/dateUtils';

interface Props {
    vacations: VacationType[];
    updateVacations?: () => void;
}

/* eslint-disable-next-line */
const RejectedVacations: React.FC<Props> = ({ vacations }) => {
    
    /* eslint-disable-next-line */
    if (!vacations || vacations.length === 0) {
        return <Message className='Vacation__Message'>You do not have any rejected vacations.</Message>;
    }

    return (
        <div className="requested-vacation">
            <div className='Table_Container'>
                <Table celled inverted selectable striped>
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell>Requested at</Table.HeaderCell>
                            <Table.HeaderCell>Start date</Table.HeaderCell>
                            <Table.HeaderCell>End date</Table.HeaderCell>
                            <Table.HeaderCell>Note</Table.HeaderCell>
                            <Table.HeaderCell>Reviewed by</Table.HeaderCell>
                            <Table.HeaderCell>Reviewed at</Table.HeaderCell>
                            <Table.HeaderCell>Rejection note</Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>

                    <Table.Body>
                        {/* eslint-disable-next-line */}
                        {vacations.map((vacation) => (
                            <Table.Row key={vacation.id}>
                                <Table.Cell>{formatDateTime(vacation.requestedAt)}</Table.Cell>
                                <Table.Cell>{formatDateTime(vacation.dateFrom, true)}</Table.Cell>
                                <Table.Cell>{formatDateTime(vacation.dateTo, true)}</Table.Cell>
                                <Table.Cell>{vacation.note}</Table.Cell>
                                <Table.Cell>{`${vacation.reviewedBy.firstName} ${vacation.reviewedBy.lastName}`}</Table.Cell>
                                <Table.Cell>{formatDateTime(vacation.reviewedAt)}</Table.Cell>
                                <Table.Cell>{vacation.rejectionNote}</Table.Cell>
                            </Table.Row>
                        ))}
                    </Table.Body>
                </Table>
            </div>
        </div>
    );
};

export default RejectedVacations;