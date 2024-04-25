import { useState, useEffect } from "react";
import { useParams, useNavigate } from 'react-router-dom';
import employeeService from '../services/employee-service';
import { Button, Dimmer, Form, FormInput, Loader, Segment } from "semantic-ui-react";
import { EmployeeType } from '../services/types';
import handleError from "../services/handler";
import errorProcessor from "../services/errorProcessor";
import '../styles/employee-list.scss'

const UpdateEmployee: React.FC = () => {
    const navigate = useNavigate();
    const { id } = useParams<{ id: string }>();
    const [employee, setEmployee] = useState<Partial<EmployeeType>>({
        id,
        firstName: '',
        lastName: '',
        phoneNumber: ''
    });
    
    /* eslint-disable-next-line */
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});

    useEffect(() => {
        const fetchEmployee = async () => {
            try {
                setLoading(true);
                const employeeData = await employeeService.getEmployeeById(id);
                setEmployee(employeeData);
            } catch (error) {
                handleError(error);
                setError('Error: ' + (error as Error).message);
                navigate(-1);
            } finally {
                setLoading(false);
            }
        };

        fetchEmployee();
    }, [id]);

    const handleUpdate = async () => {
        try {
            setFormErrors({});
            await employeeService.updateEmployee(id, employee);
            navigate(-1);
        } catch (error) {
            errorProcessor(error, setError, setFormErrors)
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        
        if (value.trim() === '') {
            setFormErrors(prevErrors => ({
                ...prevErrors,
                [name]: 'Field should not be empty'
            }));
        } 
        
        setEmployee(prevEmployee => ({
            ...prevEmployee,
            [name]: value
        }));
    };

    const handleCancel = () => {
        navigate(-1);
    };

    return (
        <div style={{ margin: '3rem auto', maxWidth: '500px' }}>
            <h1>Update Employee</h1>
            <div className="loader-container">
                <Segment inverted>
                    {loading && (
                        <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }} >
                            <Loader>Loading</Loader>
                        </Dimmer>
                    )}
                    <Form inverted>
                        <Form.Group widths='equal'>
                            <FormInput 
                                fluid 
                                label='First name' 
                                placeholder='First name' 
                                name="firstName" 
                                value={employee.firstName} 
                                onChange={handleChange}
                                error={formErrors['firstName']}
                            />
                            <FormInput 
                                fluid 
                                label='Last name' 
                                placeholder='Last name' 
                                name="lastName" 
                                value={employee.lastName} 
                                onChange={handleChange}
                                error={formErrors['lastName']}
                            />
                        </Form.Group>
                        <FormInput 
                            fluid 
                            label='Phone number' 
                            placeholder='Phone number' 
                            name="phoneNumber" 
                            value={employee.phoneNumber} 
                            onChange={handleChange}
                            error={formErrors['phoneNumber']}
                        />
                        <Button type='button' onClick={handleUpdate}>Submit</Button>
                        <Button type='button' onClick={handleCancel}>Cancel</Button>
                    </Form>
                </Segment>
            </div>
        </div>
    );
};

export default UpdateEmployee;