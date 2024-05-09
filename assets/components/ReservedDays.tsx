import {useState, useEffect} from 'react';
import reservedDayService from '../services/reserved-day-service';
import { Dimmer, Label, ListItem, Loader, Table } from 'semantic-ui-react';
import '../styles/app.scss';
import { ReservedDayType } from '../services/types';
import { formatDateTime } from './utils/dateUtils';
import { invertColor } from './utils/invertColor';

const ReservedDays: React.FC = () => {
    const [reservedDays, setReservedDays] = useState<ReservedDayType[]>([]);
    /* eslint-disable-next-line */
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);

    const fetchReservedDays = async () => {
        try {
            setLoading(true);

            const currentYear = new Date().getFullYear();
            const firstDayOfYear = new Date(`${currentYear}-01-01`);
            const lastDayOfYear = new Date(`${currentYear}-12-31`);

            const startDate = firstDayOfYear.toISOString().split('T')[0];
            const endDate = lastDayOfYear.toISOString().split('T')[0];

            const results = await reservedDayService.getReservedDays(startDate, endDate);
            setReservedDays(results);
        } catch (error) {
            setError('Error' + (error as Error).message);
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchReservedDays();
    }, []);

    return (
        <div className="reserved-days-list Content__Container">
            <h1>Reserved days</h1>
            <div className="loader-container">
                {loading && (
                    <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
                        <Loader>Loading</Loader>
                    </Dimmer>
                )}
                <div>
                    <Table celled inverted selectable striped>
                        <Table.Header>
                            <Table.Row>
                                <Table.HeaderCell>Start date</Table.HeaderCell>
                                <Table.HeaderCell>End date</Table.HeaderCell>
                                <Table.HeaderCell>Tags</Table.HeaderCell>
                                <Table.HeaderCell>Note</Table.HeaderCell>
                            </Table.Row>
                        </Table.Header>

                        <Table.Body>
                            {reservedDays.map((reservedDay) => (
                                <Table.Row key={reservedDay.id}>
                                    <Table.Cell>{formatDateTime(reservedDay.dateFrom, true)}</Table.Cell>
                                    <Table.Cell>{formatDateTime(reservedDay.dateTo, true)}</Table.Cell>
                                    <Table.Cell>
                                        {reservedDay.tags.map((tag) => (
                                            <ListItem key={tag.id} className='List__Item'>
                                                <Label style={{ backgroundColor: tag.colorCode }} horizontal>
                                                    <span style={{ color: invertColor(tag.colorCode) }}>{tag.name}</span>
                                                </Label>
                                            </ListItem>
                                        ))}
                                    </Table.Cell>
                                    <Table.Cell>{reservedDay.note}</Table.Cell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                </div>
            </div>
        </div>
    );
};

export default ReservedDays;