import { VacationType } from '../../services/types';
import { Message, Table } from 'semantic-ui-react';

interface Props {
    vacations: VacationType[];
}

const RejectedVacations: React.FC<Props> = ({ vacations }) => {
    
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
    
    if (!vacations || vacations.length === 0) {
        return <Message>There are no rejected vacation requests.</Message>;
    }

    return (
        <div className="requested-vacation">
            <div style={{ marginRight: '2rem' }}>
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
                            <Table.HeaderCell>Rejection note</Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>

                    <Table.Body>
                        {vacations.map((vacation) => (
                            <Table.Row key={vacation.id}>
                                <Table.Cell>{`${vacation.requestedBy.firstName} ${vacation.requestedBy.lastName}`}</Table.Cell>
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