import { useState, useEffect } from "react";
import { useParams, useNavigate, Link } from 'react-router-dom';
import employeeService from '../services/employee-service';

interface Employee {
    id: string;
    email: string;
    roles: string[];
    firstName: string;
    lastName: string;
    phoneNumber: string;
}

const UpdateEmployee: React.FC = () => {
    const navigate = useNavigate();
    const { id } = useParams<{ id: string }>();
    const [employee, setEmployee] = useState<Partial<Employee>>({
        id: id,
        firstName: '',
        lastName: '',
        phoneNumber: ''
    });
    const [error, setError] = useState<string>('');

    useEffect(() => {
        const fetchEmployee = async () => {
            try {
                const employeeData = await employeeService.getEmployeeById(id);
                setEmployee(employeeData);
            } catch (error) {
                setError('Unauthorized. ' + (error as Error).message);
            }
        };

        fetchEmployee();
    }, [id]);

    const handleUpdate = async () => {
        try {
            await employeeService.updateEmployee(id, employee);
            navigate('/employees');
        } catch (error) {
            setError('Error updating employee: ' + (error as Error).message);
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setEmployee(prevEmployee => ({
            ...prevEmployee,
            [name]: value
        }));
    };

    return (
        <div>
            <h1>Update Employee</h1>
            {error && <p>{error}</p>}
            <form>
                <label>
                    First Name:
                    <input type="text" name="firstName" value={employee.firstName} onChange={handleChange} />
                </label>
                <label>
                    Last Name:
                    <input type="text" name="lastName" value={employee.lastName} onChange={handleChange} />
                </label>
                <label>Phone Number:
                    <input type="tel" name="phoneNumber" value={employee.phoneNumber} onChange={handleChange} />
                </label>
                <button type="button" onClick={handleUpdate}>Update</button>
            </form>
            <Link to={`/employees/`}>Cancel</Link>
        </div>
    );
};

export default UpdateEmployee;