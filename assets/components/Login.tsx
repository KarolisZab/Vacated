import { useState, ChangeEvent, FormEvent, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button, Form, Grid, Header, Icon, Message, Modal, Segment } from 'semantic-ui-react';
import authService from '../services/auth-service';
import '../styles/login.scss';

const Login: React.FC = () => {
    const navigate = useNavigate();
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const [error, setError] = useState<string>('');
    const [isLoading, setLoading] = useState<boolean>(false);
    const [showModal, setShowModal] = useState<boolean>(false);
    const [resetEmail, setResetEmail] = useState<string>('');
    const [resetError, setResetError] = useState<string>('');
    const [resetSuccess, setResetSuccess] = useState<string>('');

    useEffect(() => {
        const checkAuthentication = async () => {
            if (authService.isAuthenticated()) {
                navigate('/');
            }
        };
        
        checkAuthentication();
    }, [navigate]);

    const handleChangeEmail = (e: ChangeEvent<HTMLInputElement>) => {
        setEmail(e.target.value);
    };

    const handleChangePassword = (e: ChangeEvent<HTMLInputElement>) => {
        setPassword(e.target.value);
    };

    const handleSubmit = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        try {
            setLoading(true);
            await authService.login(email, password);
            navigate('/');
        } catch (error) {
            setError('Invalid email or password');
        } finally {
            setLoading(false);
        }
    };

    const handleForgotPassword = () => {
        setShowModal(true);
    };

    const handleCloseModal = () => {
        setShowModal(false);
        setResetEmail('');
        setResetError('');
        setResetSuccess('');
    };

    const handleResetPassword = async () => {
        try {
            setResetError('');
            setResetSuccess('');

            if (!resetEmail) {
                setResetError('Please enter your email address');
                return;
            }

            await authService.resetPassword(resetEmail);
            setResetSuccess('Password reset email sent successfully');
        } catch (error) {
            setResetSuccess('');
            setResetError('User with this email does not exist');
        }
    };

    const handleChangeResetEmail = (e: ChangeEvent<HTMLInputElement>) => {
        setResetEmail(e.target.value);
    };

    return (
        <Grid textAlign='center' style={{ height: '90vh' }} verticalAlign='middle'>
            <Grid.Column style={{ maxWidth: 450 }}>
                <Header as='h2' color='teal' textAlign='center'>
                    Log-in to your account
                </Header>
                <Form size='large' onSubmit={handleSubmit} error={!!error}>
                    {error && <Message error content={error} color='black' />}
                    <Segment stacked>
                        <Form.Input
                            fluid
                            icon='user'
                            iconPosition='left'
                            placeholder='E-mail address'
                            value={email}
                            onChange={handleChangeEmail}
                            required
                        />
                        <Form.Input
                            fluid
                            icon='lock'
                            iconPosition='left'
                            placeholder='Password'
                            type='password'
                            value={password}
                            onChange={handleChangePassword}
                            required
                        />

                        <Button color='teal' fluid size='large' type='submit' loading={isLoading}>
                            Login
                        </Button>
                        <Button basic fluid onClick={handleForgotPassword} color='teal' >
                            Forgot password?
                        </Button>
                    </Segment>
                </Form>
                <Button color='google plus' fluid onClick={() => (window.location.href='/oauth')}>
                    <Icon name='google' />
                    Log-in with Google
                </Button>

                <Modal
                    size='small'
                    open={showModal}
                    onClose={handleCloseModal}
                    closeIcon
                >
                    <Modal.Header>Forgot your password?</Modal.Header>
                    <Modal.Content>
                        {resetSuccess && <Message success content={resetSuccess} className='custom-message' />}
                        {resetError && <Message error content={resetError} className='custom-message' />}
                        <Form>
                            <Form.Field>
                                <label>If you've forgotten your password, please enter your email address below. We'll send you an email with instructions on how to reset your password.</label>
                                <input
                                    placeholder='Enter your email'
                                    value={resetEmail}
                                    onChange={handleChangeResetEmail}
                                />
                            </Form.Field>
                        </Form>
                    </Modal.Content>
                    <Modal.Actions>
                        <Button color='teal' onClick={handleResetPassword}>
                            Reset my password
                        </Button>
                    </Modal.Actions>
                </Modal>
            </Grid.Column>
        </Grid>
    );
};

export default Login;