import { useState, ChangeEvent, FormEvent, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button, Dropdown, DropdownProps, Form, Grid, Header, Message, Segment } from 'semantic-ui-react';
import authService from '../services/auth-service';
import { EmployeeRegistrationData, TagType } from '../services/types';
import errorProcessor from '../services/errorProcessor';
import tagService from '../services/tag-service';


const Register: React.FC = () => {
    
    const navigate = useNavigate();
    const [registrationData, setRegistrationData] = useState<EmployeeRegistrationData>({
        email: '',
        password: '',
        confirmPassword: '',
        firstName: '',
        lastName: '',
        phoneNumber: '',
        tags: []
    });
    const [error, setError] = useState<string>('');
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});
    const [tags, setTags] = useState<TagType[]>([]);

    useEffect(() => {
        fetchTags();
    }, []);

    const fetchTags = async () => {
        try {
            const tags = await tagService.getAllTags();
            setTags(tags);
        } catch (error) {
            setError('Error: ' + (error as Error).message);
        }
    };

    const handleChange = (e: ChangeEvent<HTMLInputElement>, field: keyof EmployeeRegistrationData) => {
        setRegistrationData({
            ...registrationData,
            [field]: e.target.value
        });
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (registrationData.password !== registrationData.confirmPassword) {
            setError('Passwords do not match');
            return;
        }
        try {
            await authService.register(registrationData);
            navigate('/');
        } catch (error) {
            errorProcessor(error, setError, setFormErrors);
        }
    };

    const handleTagsChange = (e: React.SyntheticEvent<HTMLElement, Event>, { value }: DropdownProps) => {
        if (Array.isArray(value)) {
            const selectedTags: TagType[] = value.map(tagName => {
                const tag = tags.find(tag => tag.name === tagName);
                if (tag) {
                    return tag;
                } else {
                    return { id: '', name: '', colorCode: '' };
                }
            });
            setRegistrationData({ ...registrationData, tags: selectedTags });
        }
    };

    return (
        <Grid textAlign='center' style={{ height: '90vh' }} verticalAlign='middle'>
            <Grid.Column style={{ maxWidth: 450 }}>
                <Header as='h2' color='teal' textAlign='center'>
                    Register an account
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
                            icon='lock'
                            iconPosition='left'
                            placeholder='Password'
                            type='password'
                            value={registrationData.password}
                            onChange={(e) => handleChange(e, 'password')}
                            error={formErrors['password']}
                            required
                        />
                        <Form.Input
                            fluid
                            icon='lock'
                            iconPosition='left'
                            placeholder='Confirm Password'
                            type='password'
                            value={registrationData.confirmPassword}
                            onChange={(e) => handleChange(e, 'confirmPassword')}
                            error={formErrors['confirmPassword']}
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
                        <Form.Field>
                            <Dropdown
                                placeholder="Select tags"
                                fluid
                                multiple
                                search
                                selection
                                options={tags.map(tag => ({ key: tag.id, text: tag.name, value: tag.name }))}
                                onChange={handleTagsChange}
                                value={registrationData.tags.map(tag => tag.name)}
                            />
                        </Form.Field>
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