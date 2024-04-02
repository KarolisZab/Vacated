import { useState, useEffect } from "react";
import { useParams, useNavigate, Link } from 'react-router-dom';
import employeeService from '../services/employee-service';
import authService from "../services/auth-service";
import { Button, Form, FormCheckbox, FormInput, Segment } from "semantic-ui-react";

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
            navigate('/');
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

    const handleCancel = () => {
        navigate('/employees');
    };

    return (
        <div style={{ margin: '3rem auto', maxWidth: '500px' }}>
            <h1>Update Employee</h1>
            {error && <p>{error}</p>}
            <Segment inverted>
                <Form inverted>
                    <Form.Group widths='equal'>
                        <FormInput 
                            fluid 
                            label='First name' 
                            placeholder='First name' 
                            name="firstName" 
                            value={employee.firstName} 
                            onChange={handleChange} 
                        />
                        <FormInput 
                            fluid 
                            label='Last name' 
                            placeholder='Last name' 
                            name="lastName" 
                            value={employee.lastName} 
                            onChange={handleChange} 
                        />
                    </Form.Group>
                    <FormInput 
                        fluid 
                        label='Phone number' 
                        placeholder='Phone number' 
                        name="phoneNumber" 
                        value={employee.phoneNumber} 
                        onChange={handleChange} 
                    />
                    <Button type='button' onClick={handleUpdate}>Submit</Button>
                    <Button type='button' onClick={handleCancel}>Cancel</Button>
                </Form>
            </Segment>
        </div>
    );
};

export default UpdateEmployee;