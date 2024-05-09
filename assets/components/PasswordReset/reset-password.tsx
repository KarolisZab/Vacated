import { useState, ChangeEvent, FormEvent, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button, Form, Grid, Header, Message, Segment } from 'semantic-ui-react';
import errorProcessor from '../../services/errorProcessor';
import apiService from '../../services/api-service';
import employeeService from '../../services/employee-service';


const ResetPassword: React.FC = () => {
    const navigate = useNavigate();
    const [newPassword, setNewPassword] = useState('');
    const [confirmNewPassword, setConfirmNewPassword] = useState('');
    const [error, setError] = useState<string>('');
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});
    const [validToken, setValidToken] = useState<boolean>(false);
    const [loading, setLoading] = useState<boolean>(false);

    const handleChangeNewPassword = (e: ChangeEvent<HTMLInputElement>) => {
        setNewPassword(e.target.value);
    };

    useEffect(() => {
        const validateToken = async () => {
            const token = new URLSearchParams(location.search).get('token');
            if (token) {
                await apiService.get(`/validate-reset-token/${token}`)
                    .then(() => setValidToken(true))
                    .catch(() => setValidToken(false));
            }
        }

        validateToken();
    }, []);

    const handleChangeConfirmNewPassword = (e: ChangeEvent<HTMLInputElement>) => {
        setConfirmNewPassword(e.target.value);
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        if (newPassword !== confirmNewPassword) {
            setFormErrors({ confirmNewPassword: 'Passwords does not match' });
            setLoading(false);
            return;
        }

        setLoading(true);
        try {
            const token = new URLSearchParams(location.search).get('token');
            if (!token) {
                throw new Error('Invalid token.');
            }
            await employeeService.resetPassword(token, newPassword);
            navigate('/login');
        } catch (error) {
            errorProcessor(error, setError, setFormErrors);
        } finally {
            setLoading(false);
        }
    };

    return (
        <Grid textAlign='center' style={{ height: '90vh' }} verticalAlign='middle'>
            <Grid.Column style={{ maxWidth: 450 }}>
                <Header as='h2' color='teal' textAlign='center'>
                    Reset Password
                </Header>
                {validToken ? (
                    <Form size='large' onSubmit={handleSubmit} error={!!error}>
                        {error && <Message error style={{ backgroundColor: 'rgb(31, 31, 32)' }} content={error} />}
                        <Segment stacked>
                            <Form.Input
                                fluid
                                icon='lock'
                                iconPosition='left'
                                placeholder='New Password'
                                type='password'
                                value={newPassword}
                                onChange={handleChangeNewPassword}
                                error={formErrors['newPassword']}
                                required
                            />
                            <Form.Input
                                fluid
                                icon='lock'
                                iconPosition='left'
                                placeholder='Confirm New Password'
                                type='password'
                                value={confirmNewPassword}
                                onChange={handleChangeConfirmNewPassword}
                                error={formErrors['confirmNewPassword']}
                                required
                            />
                            <Button color='teal' fluid size='large' type='submit' loading={loading}>
                                Reset Password
                            </Button>
                        </Segment>
                    </Form>
                ) : (
                    <Message error content="Invalid token"/>
                )}
            </Grid.Column>
        </Grid>
    );
};

export default ResetPassword;