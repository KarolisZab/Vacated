import { useEffect } from "react";
import { Dimmer, Loader } from "semantic-ui-react";
import authService from "../../services/auth-service";
import { useNavigate } from "react-router-dom";
import './styles.scss';

export default function Google() {

    // page, i kuri redirectinu, kai vyksta redirectas, ir bus tuscias page tik su loaderiu, kol suvaiksto kodai ir tokenai
    // navigate i '/'

    const navigate = useNavigate();
    
    useEffect(() => {
        const loginWithGoogle = async () => {
            const urlParams = new URLSearchParams(window.location.search);
            const code = urlParams.get('code');

            if (code) {
                await authService.loginWithCode(code);
                navigate('/');
            }
        }

        loginWithGoogle();
    }, [])

    return (
        <>
            <div className="loader-auth">
                <div className="loader-container">
                    <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
                        <Loader>Loading</Loader>
                    </Dimmer>
                </div>
            </div>
        </>
    );
}