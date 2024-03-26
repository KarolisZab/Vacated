import { useState, useEffect } from "react";
import { useParams } from 'react-router-dom';
import employeeService from '../services/employee-service';

interface Employee {
    id: string;
    email: string;
    roles: string[];
    firstName: string;
    lastName: string;
    phoneNumber: string;
}

const EmployeeDetails: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [employee, setEmployee] = useState<Employee | null>(null);
    const [error, setError] = useState<string>('');

    useEffect(() => {
        const fetchEmployee = async () => {
            try {
                const employeeData = await employeeService.getEmployeeById(id);
                setEmployee(employeeData);
            } catch (error) {
                setError('Error fetching employee: ' + (error as Error).message);
            }
        };

        fetchEmployee();
    }, [id]);
    
    if (!employee) {
        return <div>Loading...</div>;
    }

    if (error) {
        return <div>{error}</div>;
    }

    return (
        <div>
            <h1>Employee Details</h1>
            <p>ID: {employee.id}</p>
            <p>Email: {employee.email}</p>
            <p>First Name: {employee.firstName}</p>
            <p>Last Name: {employee.lastName}</p>
            <p>Phone Number: {employee.phoneNumber}</p>
        </div>
    )
};

export default EmployeeDetails;