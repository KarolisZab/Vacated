import { useState } from 'react';
import { Button, Container, Form, Message } from 'semantic-ui-react';
import './styles.scss';
import authService from '../../services/auth-service';

const ForgotPassword: React.FC = () => {
    const [email, setEmail] = useState('');
    const [message, setMessage] = useState('');
    const [isLoading, setLoading] = useState(false);

    const handleSubmit = async (e: React.FormEvent) => {
        e.preventDefault();
        setLoading(true);
        try {
            setMessage('Password reset link sent to the given email address! Check inbox for instructions.');
            await authService.forgotPassword(email);
        } catch (error) {
            setMessage('Failed to send reset password link. Please try again.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <Container>
            <h2>Forgot password?</h2>
            <p>If you&apos;ve forgotten your password, please enter your email address below. We&apos;ll send you an email with instructions on how to reset your password.</p>
            <Form onSubmit={handleSubmit}>
                {message && <Message content={message} success={!message} className='success-message'/>}
                <Form.Field>
                    <label>Email:</label>
                    <input
                        type="email"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        placeholder='Enter your email'
                        required
                    />
                </Form.Field>
                <Button type="submit" color='teal' loading={isLoading}>Submit</Button>
            </Form>
        </Container>
    );
};

export default ForgotPassword;
