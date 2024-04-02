import { useState, useEffect } from "react";
import { useNavigate, useParams } from 'react-router-dom';
import employeeService from '../services/employee-service';
import { Button, Card, Container, Divider, Header } from "semantic-ui-react";

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
    const navigate = useNavigate();

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

    const handleDelete = async (id: string) => {
        try {
            await employeeService.deleteEmployee(id);
            navigate('/employees');
        } catch (error) {
            navigate('/');
            setError('Error deleting employee: ' + (error as Error).message);
        }
    };

    const handleUpdate = (employeeId) => {
        navigate(`/employees/${employeeId}/update`);
    }
    
    if (!employee) {
        return <div>Loading...</div>;
    }

    if (error) {
        return <div>{error}</div>;
    }

    return (
        <Container style={{ marginTop: '2rem' }}>
            <Header as='h1' style={{ color: 'white'}}>Employee Details</Header>
            <Card fluid style={{ backgroundColor: '#252525'}}>
                <Card.Content>
                    <Card.Header style={{ color: 'white'}}>{employee.firstName} {employee.lastName}</Card.Header>
                    <Card.Meta style={{ color: 'white'}}>ID: {employee.id}</Card.Meta>
                    <Divider />
                    <Card.Description>
                        <p><strong>Email:</strong> {employee.email}</p>
                        <p><strong>Phone Number:</strong> {employee.phoneNumber}</p>
                    </Card.Description>
                    <Divider />
                    <div style={{ display: 'flex', justifyContent: 'space-evenly' }}>
                        <Button color='blue' onClick={() => handleUpdate(employee.id)}>Update</Button>
                        <Button color='red' onClick={() => handleDelete(employee.id)}>Delete</Button>
                    </div>
                </Card.Content>
            </Card>
        </Container>
    )
};

export default EmployeeDetails;