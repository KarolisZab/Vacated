import { useState, useEffect } from "react";
import { useNavigate, useParams } from 'react-router-dom';
import employeeService from '../services/employee-service';
import { Button, Card, Container, Dimmer, Divider, Header, Label, Loader, Message, Modal } from "semantic-ui-react";
import { EmployeeType, TagType } from '../services/types';
import { invertColor } from "./utils/invertColor";

const EmployeeDetails: React.FC = () => {
    const { id } = useParams<{ id: string }>();
    const [employee, setEmployee] = useState<EmployeeType | null>(null);
    const [error, setError] = useState<string>('');
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [deleteModalOpen, setDeleteModalOpen] = useState<boolean>(false);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchEmployee = async () => {
            try {
                setIsLoading(true);
                const employeeData = await employeeService.getEmployeeById(id);
                setEmployee(employeeData);
            } catch (error) {
                navigate('/admin/employees');
                setError('Error fetching employee: ' + (error as Error).message);
            } 
            finally {
                setIsLoading(false);
            }
        };

        fetchEmployee();
    }, [id]);

    const handleDelete = () => {
        setDeleteModalOpen(true);
    };

    const confirmDelete = async (id: string) => {
        try {
            await employeeService.deleteEmployee(id);
            navigate('/admin/employees');
        } catch (error) {
            navigate('/admin/employees');
            setError('Error deleting employee: ' + (error as Error).message);
        }
        setDeleteModalOpen(false);
    };

    const handleUpdate = (employeeId) => {
        navigate(`/admin/employees/${employeeId}/update`);
    }

    if (!employee) {
        return null;
    }

    return (
        <Container className="Content__Container">
            {isLoading && (
                <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }} >
                    <Loader>Loading</Loader>
                </Dimmer>
            )}
            <Header as='h1' style={{ color: 'white'}}>Employee Details</Header>
            {error && <Message negative>{error}</Message>}
            <Card fluid style={{ backgroundColor: '#252525'}}>
                <Card.Content>
                    <Card.Header style={{ color: 'white'}}>{employee.firstName} {employee.lastName}</Card.Header>
                    <Card.Meta style={{ color: 'white'}}>ID: {employee.id}</Card.Meta>
                    <Divider />
                    <Card.Description>
                        <p><strong>Email:</strong> {employee.email}</p>
                        <p><strong>Phone Number:</strong> {employee.phoneNumber}</p>
                        <p><strong>Available vacation days:</strong> {employee.availableDays}/20</p>
                        <p><strong>Tags:</strong></p>
                        {employee.tags?.map((tag: TagType) => (
                            <Label key={tag.id} style={{ backgroundColor: tag.colorCode }} horizontal>
                                <span style={{ color: invertColor(tag.colorCode) }}>{tag.name}</span>
                            </Label>
                        ))}
                    </Card.Description>
                    <Divider />
                    <div style={{ display: 'flex', justifyContent: 'space-evenly' }}>
                        <Button color='blue' onClick={() => handleUpdate(employee.id)}>Update</Button>
                        <Button color='red' onClick={() => handleDelete()}>Remove</Button>
                    </div>
                </Card.Content>
            </Card>

            <Modal
                open={deleteModalOpen}
                onClose={() => setDeleteModalOpen(false)}
                size='mini'
                className="modal-wrapper"
            >
                <Modal.Header className="modal-header">Confirm Delete</Modal.Header>
                <Modal.Content className="modal-content">
                    <p>Are you sure you want to delete this employee?</p>
                </Modal.Content>
                <Modal.Actions className="modal-actions">
                    <Button negative onClick={() => setDeleteModalOpen(false)}>Cancel</Button>
                    <Button positive onClick={() => confirmDelete(employee.id)}>Confirm</Button>
                </Modal.Actions>
            </Modal>
        </Container>
    )
};

export default EmployeeDetails;