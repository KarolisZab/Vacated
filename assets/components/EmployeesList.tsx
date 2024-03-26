import {useState, useEffect} from 'react';
import employeeService from '../services/employee-service';
import { Link, useNavigate } from 'react-router-dom';

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
                setError('Unauthorized. ' + (error as Error).message);
            }
        };

        fetchEmployees();
    }, []);

    const handleDelete = async (id: string) => {
        try {
            await employeeService.deleteEmployee(id);
            setEmployees(prevEmployees => prevEmployees.filter(employee => employee.id !== id));
        } catch (error) {
            setError('Error deleting employee: ' + (error as Error).message);
        }
    };

    const handleUpdate = (employeeId) => {
        navigate(`/employees/${employeeId}/update`);
    }

    const sortedEmployees = [...employees].sort((a, b) => (a.id > b.id ? 1 : -1));

    return (
        <div>
            <h1>Employee List</h1>
            {error && <p>{error}</p>}
            {sortedEmployees.map((employee) => {
                return (
                    <div key={employee.id}>
                        <p>{employee.id}</p>
                        <p>{employee.email}</p>
                        <Link to={`/employees/${employee.id}`}>View details</Link>
                        {/* <Link to={`/employees/${employee.id}/update`}>Update</Link> */}
                        <button onClick={() => handleUpdate(employee.id)}>Update</button>
                        <button onClick={() => handleDelete(employee.id)}>Delete</button>
                    </div>
                )
            })}
        </div>
    );
};

export default EmployeesList;