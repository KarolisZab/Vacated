import {useState, useEffect} from 'react';
import employeeService from '../services/employee-service';
import { Link, useNavigate } from 'react-router-dom';
import { Dimmer, Loader, Message, Table } from 'semantic-ui-react';
import '../styles/employee-list.scss'
import { EmployeeType } from '../services/types';

const EmployeesList: React.FC = () => {
    const navigate = useNavigate();
    const [employees, setEmployees] = useState<EmployeeType[]>([]);
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);

    useEffect(() => {
        const fetchEmployees = async () => {
            try {
                setLoading(true);
                const employees = await employeeService.getAllEmployees();
                setEmployees(employees);
            } catch (error) {
                setError('Error' + (error as Error).message);
                navigate("/");
            } finally {
                setLoading(false);
            }
        };

        fetchEmployees();
    }, []);

    return (
        <div className="employees-list">
            <h1>Employees</h1>
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
                                <Table.HeaderCell>Actions</Table.HeaderCell>
                            </Table.Row>
                        </Table.Header>

                        <Table.Body>
                            {employees.map((employee) => (
                                <Table.Row key={employee.id}>
                                    <Table.Cell>{employee.firstName} {employee.lastName}</Table.Cell>
                                    <Table.Cell>{employee.email}</Table.Cell>
                                    <Table.Cell>{employee.phoneNumber}</Table.Cell>
                                    <Table.Cell>
                                        <Link to={`/admin/employees/${employee.id}`}>View details</Link>
                                    </Table.Cell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                </div>
            </div>
        </div>
    );
};

export default EmployeesList;