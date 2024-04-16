import { useState, ChangeEvent, FormEvent, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import { Button, Form, Grid, Header, Message, Segment } from 'semantic-ui-react';
import authService from '../services/auth-service';
import { GoogleLogin } from '@react-oauth/google';
// import GoogleLogin, { GoogleLoginResponse, GoogleLoginResponseOffline } from 'react-google-login';

const Login: React.FC = () => {
    const navigate = useNavigate();
    const [email, setEmail] = useState<string>('');
    const [password, setPassword] = useState<string>('');
    const [error, setError] = useState<string>('');

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
            await authService.login(email, password);
            navigate('/');
        } catch (error) {
            setError('Invalid email or password');
        }
    };

    const responseGoogle = async (credentialResponse: any) => {
        try {
            // Send the Google login response to your backend for authentication
            // const googleToken = response.tokenId; // Access the Google token ID
            await authService.loginWithGoogle(credentialResponse); // Send the token to your backend
            navigate('/'); // Redirect the user to the home page after successful login
        } catch (error) {
            setError('Failed to log in with Google');
            console.error('Google login failed:', error);
        }
    };

    const responseGoogleFailure = () => {
        console.log('Failed to log in with Google');
        // You can handle the failure case here
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

                        <Button color='teal' fluid size='large' type='submit'>
                            Login
                        </Button>

                        <GoogleLogin
                            onSuccess={responseGoogle}
                            onError={responseGoogleFailure}
                        />
                    </Segment>
                </Form>
            </Grid.Column>
        </Grid>
    );
};

export default Login;