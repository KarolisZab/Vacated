import { useState, ChangeEvent, FormEvent, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button, Dropdown, DropdownProps, Form, Message, Segment } from 'semantic-ui-react';
import { EmployeeRegistrationData, TagType } from '../services/types';
import errorProcessor from '../services/errorProcessor';
import tagService from '../services/tag-service';
import employeeService from '../services/employee-service';


const Register: React.FC = () => {
    
    const navigate = useNavigate();
    const [registrationData, setRegistrationData] = useState<EmployeeRegistrationData>({
        email: '',
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
        try {
            await employeeService.createUser(registrationData);
            navigate(-1);
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

    const handleTagCreate = async (e: React.KeyboardEvent<HTMLElement>, { value }: DropdownProps) => {
        if (e.key === 'Enter' && value) {
            try {
                const newTag: TagType = { id: '', name: value as string, colorCode: '#808080' };
                
                setTags([...tags, newTag]);

                setRegistrationData({
                    ...registrationData,
                    tags: [...registrationData.tags, newTag]
                });
            } catch (error) {
                setError('Error: ' + (error as Error).message);
            }
        }
    };

    return (
        <div style={{ margin: '3rem auto', maxWidth: '500px' }} className='Content__Container'>
            <h1>Create a new employee</h1>
            <Segment inverted>
                <Form inverted onSubmit={handleSubmit} error={!!error}>
                    {error && <Message error content={error} />}
                    <Form.Group widths='equal'>
                        <Form.Input
                            fluid
                            label='E-mail address'
                            icon='user'
                            iconPosition='left'
                            placeholder='E-mail address'
                            value={registrationData.email}
                            onChange={(e) => handleChange(e, 'email')}
                            error={formErrors['email']}
                        />
                    </Form.Group>
                    <Form.Group widths='equal'>
                        <Form.Input
                            fluid
                            label='First name'
                            icon='user'
                            iconPosition='left'
                            placeholder='First Name'
                            value={registrationData.firstName}
                            onChange={(e) => handleChange(e, 'firstName')}
                            error={formErrors['firstName']}
                        />
                        <Form.Input
                            fluid
                            label='Last name'
                            icon='user'
                            iconPosition='left'
                            placeholder='Last Name'
                            value={registrationData.lastName}
                            onChange={(e) => handleChange(e, 'lastName')}
                            error={formErrors['lastName']}
                        />
                    </Form.Group>
                    <Form.Group widths='equal'>
                        <Form.Input
                            fluid
                            label='Phone number'
                            icon='phone'
                            iconPosition='left'
                            placeholder='Phone Number'
                            value={registrationData.phoneNumber}
                            onChange={(e) => handleChange(e, 'phoneNumber')}
                            error={formErrors['phoneNumber']}
                        />
                    </Form.Group>
                    <Form.Field>
                        <label>Tags</label>
                        <Dropdown
                            placeholder='Select tags'
                            fluid
                            multiple
                            search
                            selection
                            options={tags.map(tag => ({ key: tag.id, text: tag.name, value: tag.name }))}
                            onChange={handleTagsChange}
                            value={registrationData.tags.map(tag => tag.name)}
                            allowAdditions
                            onAddItem={handleTagCreate}
                        />
                    </Form.Field>
                    <Button color='teal' fluid size='large' type='submit'>
                        Create
                    </Button>
                </Form>
            </Segment>
        </div>
    );
};

export default Register;