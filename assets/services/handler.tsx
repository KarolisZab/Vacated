import { AxiosError } from "axios";
import authService from "./auth-service";
import { useNavigate } from "react-router-dom";

const handleError = (error: AxiosError): void => {
    const navigate = useNavigate();
    if (error.response?.status === 401) {
        authService.logout();
    }
    else if (error.response?.status >= 500) {
        navigate('/500');
    }
    else if (error.response?.status === 403) {
        // TODO: figure out, kaip handlint sita. Maybe atskira page, arba issiaiskint, kaip isvest pranesima, kuri butu imanoma
        // vaizduoti componente
        navigate(-1);
    }
};

export default handleError;
