import { useState, useEffect } from "react";
import { useParams, useNavigate } from 'react-router-dom';
import employeeService from '../../services/employee-service';
import { Button, Dimmer, Divider, Dropdown, DropdownProps, Form, FormInput, Loader, Progress, Segment, SemanticCOLORS } from "semantic-ui-react";
import { EmployeeType, TagType } from '../../services/types';
import handleError from "../../services/handler";
import errorProcessor from "../../services/errorProcessor";
import './styles.scss'
import tagService from "../../services/tag-service";

const Profile: React.FC = () => {
    const navigate = useNavigate();
    const [employee, setEmployee] = useState<Partial<EmployeeType>>({
        id: '',
        firstName: '',
        lastName: '',
        email: '',
        phoneNumber: '',
        tags: []
    });
    const [tags, setTags] = useState<TagType[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});
    /* eslint-disable-next-line */
    const [error, setError] = useState<string>('');
    const [oldPassword, setOldPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmNewPassword, setConfirmNewPassword] = useState('');
    const [passwordErrors, setPasswordErrors] = useState<{ [key: string]: string }>({});

    useEffect(() => {
        const fetchEmployee = async () => {
            try {
                const employeeData = await employeeService.getCurrentUser();
                setEmployee(employeeData);
            } catch (error) {
                handleError(error);
                navigate(-1);
            } finally {
                setLoading(false);
            }
        };

        fetchEmployee();
        fetchTags();
    }, []);

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

            await employeeService.updateEmployee(employee.id, employee);
            navigate(-1);
        } catch (error) {
            errorProcessor(error, setError, setFormErrors);
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
        navigate('/');
    };

    const handleTagCreate = async (e: React.KeyboardEvent<HTMLElement>, { value }: DropdownProps) => {
        if (e.key === 'Enter' && value) {
            try {
                const newTag: TagType = { id: '', name: value as string, colorCode: 'grey' };
                setTags([...tags, newTag]);
                setEmployee({ ...employee, tags: [...employee.tags, newTag] });
            } catch (error) {
                handleError(error);
            }
        }
    };

    const handleChangePassword = async () => {
        setPasswordErrors({});
        
        if (newPassword !== confirmNewPassword) {
            setPasswordErrors(prevErrors => ({
                ...prevErrors,
                confirmNewPassword: 'New password and confirm password do not match'
            }));
            return;
        }

        try {
            await employeeService.changePassword(oldPassword, newPassword);
            navigate('/');
        } catch (error) {
            setPasswordErrors(prevErrors => ({
                ...prevErrors,
                confirmNewPassword: 'Failed to change password'
            }));
            handleError(error);
        }
    };

    const getColor = (days: number): SemanticCOLORS => {
        if (days <= 7) {
            return 'red';
        } else if (days <= 13) {
            return 'yellow';
        } else {
            return 'green';
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
                        <FormInput 
                            fluid 
                            label='Email' 
                            placeholder='Email' 
                            name="email" 
                            value={employee.email} 
                            className={'disabled'}
                            readOnly
                        />
                        {employee.availableDays !== undefined && (
                            <>
                                <p>Available vacation days:</p>
                                <Progress value={employee.availableDays} total='20' progress='ratio' size='medium' color={getColor(employee.availableDays)} />
                            </> 
                        )}
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
                        <Button type='button' onClick={handleUpdate}>Save changes</Button>
                        <Button type='button' onClick={handleCancel}>Return</Button>
                    </Form>
                    <Divider />
                    <Form inverted>
                        <FormInput
                            fluid
                            label='Old Password'
                            placeholder='Old Password'
                            type='password'
                            value={oldPassword}
                            onChange={(e) => setOldPassword(e.target.value)}
                            error={passwordErrors['oldPassword']}
                        />
                        <FormInput
                            fluid
                            label='New Password'
                            placeholder='New Password'
                            type='password'
                            value={newPassword}
                            onChange={(e) => setNewPassword(e.target.value)}
                            error={passwordErrors['newPassword']}
                        />
                        <FormInput
                            fluid
                            label='Confirm New Password'
                            placeholder='Confirm New Password'
                            type='password'
                            value={confirmNewPassword}
                            onChange={(e) => setConfirmNewPassword(e.target.value)}
                            error={passwordErrors['confirmNewPassword']}
                        />
                        <Button type='button' onClick={handleChangePassword}>Change password</Button>
                    </Form>
                </Segment>
            </div>
        </div>
    );
};

export default Profile;
