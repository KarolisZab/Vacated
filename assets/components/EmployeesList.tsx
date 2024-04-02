import {useState, useEffect} from 'react';
import employeeService from '../services/employee-service';
import { Link, useNavigate } from 'react-router-dom';
import { Button, Card, List, Message, Table } from 'semantic-ui-react';
import '../styles/employee-list.scss'

interface Employee {
    id: string;
    email: string;
    roles: string[];
    firstName: string;
    lastName: string;
    phoneNumber: string;
}

const EmployeesList: React.FC = () => {
    const navigate = useNavigate();
    const [employees, setEmployees] = useState<Employee[]>([]);
    const [error, setError] = useState<string>('');

    useEffect(() => {
        const fetchEmployees = async () => {
            try {
                const employees = await employeeService.getAllEmployees();
                setEmployees(employees);
            } catch (error) {
                navigate("/");
                setError('Unauthorized. ' + (error as Error).message);
            }
        };

        fetchEmployees();
    }, []);

    const sortedEmployees = [...(employees ?? [])].sort((a, b) => (a.id > b.id ? 1 : -1));


    return (
        <div className="employees-list">
            <h1>Employees</h1>
            {error && <Message negative>{error}</Message>}
            <div style={{ marginRight: '2rem' }}>
                <Table celled inverted selectable>
                    <Table.Header>
                        <Table.Row>
                            <Table.HeaderCell>ID</Table.HeaderCell>
                            <Table.HeaderCell>Name</Table.HeaderCell>
                            <Table.HeaderCell>Email</Table.HeaderCell>
                            <Table.HeaderCell>Phone No.</Table.HeaderCell>
                            <Table.HeaderCell></Table.HeaderCell>
                        </Table.Row>
                    </Table.Header>

                    <Table.Body>
                        {sortedEmployees.map((employee) => (
                            <Table.Row key={employee.id}>
                                <Table.Cell>{employee.id}</Table.Cell>
                                <Table.Cell>{employee.firstName} {employee.lastName}</Table.Cell>
                                <Table.Cell>{employee.email}</Table.Cell>
                                <Table.Cell>{employee.phoneNumber}</Table.Cell>
                                <Table.Cell>
                                    <Link to={`/employees/${employee.id}`}>View details</Link>
                                </Table.Cell>
                            </Table.Row>
                        ))}
                    </Table.Body>
                </Table>
            </div>
        </div>
    );
};

export default EmployeesList;