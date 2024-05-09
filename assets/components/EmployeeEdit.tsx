import { useState, useEffect } from "react";
import { useParams, useNavigate } from 'react-router-dom';
import employeeService from '../services/employee-service';
import { Button, Dimmer, Dropdown, DropdownProps, Form, FormInput, Loader, Segment } from "semantic-ui-react";
import { EmployeeType, TagType } from '../services/types';
import handleError from "../services/handler";
import errorProcessor from "../services/errorProcessor";
import '../styles/employee-list.scss'
import tagService from "../services/tag-service";

const UpdateEmployee: React.FC = () => {
    const navigate = useNavigate();
    const { id } = useParams<{ id: string }>();
    const [employee, setEmployee] = useState<Partial<EmployeeType>>({
        id,
        firstName: '',
        lastName: '',
        phoneNumber: '',
        tags: []
    });
    const [tags, setTags] = useState<TagType[]>([]);
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
        fetchTags();
    }, [id]);

    const fetchTags = async () => {
        try {
            const tagsData = await tagService.getAllTags();
            setTags(tagsData);
        } catch (error) {
            handleError(error);
            setError('Error fetching tags: ' + (error as Error).message);
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
            setEmployee({ ...employee, tags: selectedTags });
        }
    };

    const handleUpdate = async () => {
        setLoading(true);
        try {
            const fieldErrors: { [key: string]: string } = {};
            if (employee.firstName.trim() === '') {
                fieldErrors['firstName'] = 'Field should not be empty';
            }
            if (employee.lastName.trim() === '') {
                fieldErrors['lastName'] = 'Field should not be empty';
            }
            if (employee.phoneNumber.trim() === '') {
                fieldErrors['phoneNumber'] = 'Field should not be empty';
            }

            if (Object.keys(fieldErrors).length > 0) {
                setFormErrors(fieldErrors);
                return;
            }

            setFormErrors({});
            await employeeService.updateEmployee(id, employee);
            navigate(-1);
        } catch (error) {
            errorProcessor(error, setError, setFormErrors)
        } finally {
            setLoading(false);
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

    const handleTagCreate = async (e: React.KeyboardEvent<HTMLElement>, { value }: DropdownProps) => {
        if (e.key === 'Enter' && value) {
            try {
                const newTag: TagType = { id: '', name: value as string, colorCode: '#808080' };
                
                setTags([...tags, newTag]);

                setEmployee({ ...employee, tags: [...employee.tags, newTag] });
            } catch (error) {
                setError('Error fetching tags: ' + (error as Error).message);
            }
        }
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
                        <Form.Field>
                            <label>Tags</label>
                            <Dropdown
                                placeholder='Select tags'
                                fluid
                                multiple
                                search
                                selection
                                options={tags.map(tag => ({ key: tag.id, text: tag.name, value: tag.name }))}
                                value={employee.tags.map(tag => tag.name)}
                                onChange={handleTagsChange}
                                allowAdditions
                                onAddItem={handleTagCreate}
                            />
                        </Form.Field>
                        <Button type='button' color="blue" loading={loading} onClick={handleUpdate}>Update</Button>
                        <Button type='button' onClick={handleCancel}>Back</Button>
                    </Form>
                </Segment>
            </div>
        </div>
    );
};

export default UpdateEmployee;