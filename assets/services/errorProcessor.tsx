import { AxiosError } from "axios";

const errorProcessor = (error: AxiosError, setError: React.Dispatch<React.SetStateAction<string>>, setFormErrors: React.Dispatch<React.SetStateAction<{ [key: string]: string }>>): void => {
    if (error.response?.status === 400) {
        const errorResponse = error.response.data;
        if (typeof errorResponse === 'object' && errorResponse !== null) {
            setFormErrors(errorResponse as { [key: string]: string });
        } else {
            setError('Error updating employee: An error occurred');
        }
    }
};

export default errorProcessor;