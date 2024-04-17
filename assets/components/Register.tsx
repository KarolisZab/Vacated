import { useState, ChangeEvent, FormEvent } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button, Form, Grid, Header, Message, Segment } from 'semantic-ui-react';
import { EmployeeRegistrationData } from '../services/types';
import errorProcessor from '../services/errorProcessor';
import employeeService from '../services/employee-service';


const Register: React.FC = () => {
    
    const navigate = useNavigate();
    const [registrationData, setRegistrationData] = useState<EmployeeRegistrationData>({
        email: '',
        firstName: '',
        lastName: '',
        phoneNumber: ''
    });
    const [error, setError] = useState<string>('');
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});

    const handleChange = (e: ChangeEvent<HTMLInputElement>, field: keyof EmployeeRegistrationData) => {
        setRegistrationData({
            ...registrationData,
            [field]: e.target.value
        });
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        try {
            await employeeService.createUser(registrationData);
            navigate(-1);
        } catch (error) {
            errorProcessor(error, setError, setFormErrors);
        }
    };

    return (
        <Grid textAlign='center' style={{ height: '90vh' }} verticalAlign='middle'>
            <Grid.Column style={{ maxWidth: 450 }}>
                <Header as='h2' color='teal' textAlign='center'>
                    Create a new user account
                </Header>
                <Form size='large' onSubmit={handleSubmit} error={!!error}>
                    {error && <Message error style={{ backgroundColor: 'rgb(31, 31, 32)' }} content={error} />}
                    <Segment stacked>
                        <Form.Input
                            fluid
                            icon='user'
                            iconPosition='left'
                            placeholder='E-mail address'
                            value={registrationData.email}
                            onChange={(e) => handleChange(e, 'email')}
                            error={formErrors['email']}
                            required
                        />
                        <Form.Input
                            fluid
                            icon='user'
                            iconPosition='left'
                            placeholder='First Name'
                            value={registrationData.firstName}
                            onChange={(e) => handleChange(e, 'firstName')}
                            error={formErrors['firstName']}
                            required
                        />
                        <Form.Input
                            fluid
                            icon='user'
                            iconPosition='left'
                            placeholder='Last Name'
                            value={registrationData.lastName}
                            onChange={(e) => handleChange(e, 'lastName')}
                            error={formErrors['lastName']}
                            required
                        />
                        <Form.Input
                            fluid
                            icon='phone'
                            iconPosition='left'
                            placeholder='Phone Number'
                            value={registrationData.phoneNumber}
                            onChange={(e) => handleChange(e, 'phoneNumber')}
                            error={formErrors['phoneNumber']}
                            required
                        />
                        <Button color='teal' fluid size='large' type='submit'>
                            Register
                        </Button>
                    </Segment>
                </Form>
            </Grid.Column>
        </Grid>
    );
};

export default Register;