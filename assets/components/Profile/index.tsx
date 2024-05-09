import { useState, useEffect } from "react";
import { useNavigate } from 'react-router-dom';
import employeeService from '../../services/employee-service';
import { Button, Dimmer, Divider, Form, FormInput, Label, Loader, Message, Progress, Segment, SemanticCOLORS } from "semantic-ui-react";
import { EmployeeType } from '../../services/types';
import handleError from "../../services/handler";
import errorProcessor from "../../services/errorProcessor";
import './styles.scss'
import { invertColor } from "../utils/invertColor";

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
    const [loading, setLoading] = useState<boolean>(true);
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});
    /* eslint-disable-next-line */
    const [error, setError] = useState<string>('');
    const [oldPassword, setOldPassword] = useState('');
    const [newPassword, setNewPassword] = useState('');
    const [confirmNewPassword, setConfirmNewPassword] = useState('');
    const [passwordErrors, setPasswordErrors] = useState<{ [key: string]: string }>({});
    const [successMessage, setSuccessMessage] = useState<string>('');

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
    }, []);

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

            await employeeService.updateProfile(employee.id, employee);
            setSuccessMessage('Profile updated successfully.');
        } catch (error) {
            errorProcessor(error, setError, setFormErrors);
        } finally {
            setLoading(false);
        }
    };

    const handleChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const { name, value } = e.target;
        setEmployee(prevEmployee => ({
            ...prevEmployee,
            [name]: value
        }));
    };

    const handleChangePassword = async () => {
        setPasswordErrors({});
        setLoading(true);
        if (newPassword !== confirmNewPassword) {
            setPasswordErrors(prevErrors => ({
                ...prevErrors,
                confirmNewPassword: 'New password and confirm password do not match'
            }));
            setLoading(false);
            return;
        }

        try {
            await employeeService.changePassword(oldPassword, newPassword);
            setSuccessMessage('Password changed successfully.');
        } catch (error) {
            setPasswordErrors(prevErrors => ({
                ...prevErrors,
                confirmNewPassword: 'Failed to change password'
            }));
            handleError(error);
        } finally {
            setLoading(false);
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
            <h1>Profile</h1>
            {successMessage && (
                <Message success content={successMessage} />
            )}
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
                            label='E-mail address' 
                            placeholder='E-mail address' 
                            name="email" 
                            value={employee.email} 
                            className={'Text__Input--disabled'}
                            disabled
                        />
                        {employee.availableDays !== undefined && (
                            <>
                                <p>Available vacation days</p>
                                <Progress value={employee.availableDays} total='20' progress='ratio' size='medium' color={getColor(employee.availableDays)} />
                            </> 
                        )}
                        {employee.tags && employee.tags.length > 0 && (
                            <>
                                <div className="Profile__TagsContainer">
                                    <p>Tags</p>
                                    {employee.tags.map((tag) => (
                                        <Label key={tag.id} style={{ backgroundColor: tag.colorCode }} horizontal>
                                            <span style={{ color: invertColor(tag.colorCode) }}>{tag.name}</span>
                                        </Label>
                                    ))}
                                </div>
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
                        <Button type='button' loading={loading} onClick={handleUpdate}>Save changes</Button>
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
                        <Button type='button' loading={loading} onClick={handleChangePassword}>Change password</Button>
                    </Form>
                </Segment>
            </div>
        </div>
    );
};

export default Profile;
