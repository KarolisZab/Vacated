import { AxiosError } from "axios";
import authService from "./auth-service";
import { useNavigate } from "react-router-dom";

const handleError = (error: AxiosError): void => {
    if (error.response?.status === 401) {
        authService.logout();
    }
};

export default handleError;
