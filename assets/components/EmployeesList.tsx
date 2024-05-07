import {useState, useEffect} from 'react';
import employeeService from '../services/employee-service';
import { Link, useNavigate } from 'react-router-dom';
import { Button, Dimmer, Input, Label, ListItem, Loader, Message, Pagination, Progress, SemanticCOLORS, Table } from 'semantic-ui-react';
import '../styles/employee-list.scss'
import { EmployeeType } from '../services/types';
import { invertColor } from './utils/invertColor';

const EmployeesList: React.FC = () => {
    const navigate = useNavigate();
    const [employees, setEmployees] = useState<EmployeeType[]>([]);
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);
    const [page, setPage] = useState<number>(1);
    const [totalItems, setTotalItems] = useState<number>(0);
    const [filter, setFilter] = useState<string>('');
    /* eslint-disable-next-line */
    const [limit, setLimit] = useState<number>(10);

    useEffect(() => {
        const fetchEmployees = async () => {
            try {
                setLoading(true);
                const result = await employeeService.getEmployees(page, limit, filter);
                setEmployees(result.items);
                setTotalItems(result.totalItems);
            } catch (error) {
                setError('Error' + (error as Error).message);
                navigate("/");
            } finally {
                setLoading(false);
            }
        };

        fetchEmployees();
    }, [page, filter]);

    /* eslint-disable-next-line */
    const handlePaginationChange = (event: React.MouseEvent, data: any) => {
        setPage(data.activePage);
    };

    const handleFilterChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        setFilter(event.target.value);
    };

    const handleCreateUser = () => {
        navigate('/admin/create-user');
    }

    const getColor = (days: number): SemanticCOLORS => {
        if (days <= 7) {
            return 'red';
        } else if (days <= 13) {
            return 'yellow';
        } else {
            return 'green';
        }
    };

    return (
        <div className="employees-list Content__Container">
            <h1>Employees</h1>
            <Input inverted
                icon='search'
                placeholder='Search...'
                value={filter}
                onChange={handleFilterChange}
                style={{ marginBottom: '1rem' }}
            />
            <Button color='teal' style={{ marginLeft: '1rem' }} onClick={handleCreateUser}>
                        Create a new employee
            </Button>
            {error && <Message negative>{error}</Message>}
            <div className="loader-container">
                {loading && (
                    <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
                        <Loader>Loading</Loader>
                    </Dimmer>
                )}
                <div style={{ marginRight: '2rem' }}>
                    <Table celled inverted selectable striped>
                        <Table.Header>
                            <Table.Row>
                                <Table.HeaderCell>Name</Table.HeaderCell>
                                <Table.HeaderCell>Email</Table.HeaderCell>
                                <Table.HeaderCell>Phone No.</Table.HeaderCell>
                                <Table.HeaderCell>Tags</Table.HeaderCell>
                                <Table.HeaderCell>Available days</Table.HeaderCell>
                                <Table.HeaderCell>Actions</Table.HeaderCell>
                            </Table.Row>
                        </Table.Header>

                        <Table.Body>
                            {employees.map((employee) => (
                                <Table.Row key={employee.id}>
                                    <Table.Cell>{employee.firstName} {employee.lastName}</Table.Cell>
                                    <Table.Cell>
                                        {employee.email}
                                        {employee.admin && (
                                            <Label color='red' horizontal style={{ marginLeft: '0.5rem' }}>
                                                Admin
                                            </Label>
                                        )}
                                    </Table.Cell>
                                    <Table.Cell>{employee.phoneNumber}</Table.Cell>
                                    <Table.Cell>
                                        {employee.tags.map((tag) => (
                                            <ListItem key={tag.id} className='List__Item'>
                                                <Label style={{ backgroundColor: tag.colorCode }} horizontal>
                                                    <span style={{ color: invertColor(tag.colorCode) }}>{tag.name}</span>
                                                </Label>
                                            </ListItem>
                                        ))}
                                    </Table.Cell>
                                    <Table.Cell>
                                        <Progress value={employee.availableDays} total='20' progress='ratio' size='small' color={getColor(employee.availableDays)} />
                                    </Table.Cell>
                                    <Table.Cell>
                                        <Link to={`/admin/employees/${employee.id}`}>View details</Link>
                                    </Table.Cell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                    {totalItems > 0 && (
                        <Pagination
                            totalPages={Math.ceil(totalItems / 10)}
                            activePage={page}
                            onPageChange={handlePaginationChange}
                            size="mini"
                        />
                    )}
                </div>
            </div>
        </div>
    );
};

export default EmployeesList;