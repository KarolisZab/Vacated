import {useState, useEffect} from 'react';
import tagService from '../../services/tag-service';
import { useNavigate, useParams } from 'react-router-dom';
import { Button, Dimmer, Form, Loader, Message, Modal, Table } from 'semantic-ui-react';
import './styles.scss';
import { TagType } from '../../services/types';
import { SketchPicker } from 'react-color';

const TagsList: React.FC = () => {
    const navigate = useNavigate();
    const { id } = useParams<{ id: string }>();
    const [tags, setTags] = useState<TagType[]>([]);
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(false);
    const [modalOpen, setModalOpen] = useState(false);
    const [deleteModalOpen, setDeleteModalOpen] = useState<boolean>(false);
    const [deleteId, setDeleteId] = useState<string>('');
    const [formErrors, setFormErrors] = useState<{ [key: string]: string }>({});
    const [tagData, setTagData] = useState<Partial<TagType>>({
        id,
        name: '',
        colorCode: ''
    });
    const [newTagModalOpen, setNewTagModalOpen] = useState<boolean>(false);
    const [newTagData, setNewTagData] = useState<Partial<TagType>>({
        name: '',
        colorCode: ''
    });

    const fetchTags = async () => {
        try {
            setLoading(true);
            const tags = await tagService.getAllTags();
            setTags(tags);
        } catch (error) {
            setError('Error' + (error as Error).message);
            navigate("/");
        } finally {
            setLoading(false);
        }
    };

    useEffect(() => {
        fetchTags();
    }, []);

    useEffect(() => {
        if (!newTagModalOpen) {
            setNewTagData({
                name: '',
                colorCode: '',
            });
            setFormErrors({});
        }
    }, [newTagModalOpen]);

    const handleDelete = (id: string) => {
        setDeleteId(id);
        setDeleteModalOpen(true);
    };

    const handleUpdate = async (event: React.MouseEvent<HTMLButtonElement, MouseEvent>, id: string) => {
        event.preventDefault();
        try {
            await tagService.updateTag(id, tagData);
            closeModal();
            fetchTags();
        } catch (error) {
            setError('Error' + (error as Error).message);
        }
    };

    const handleNewTagSubmit = async () => {
        try {
            if (newTagData.name.trim() === '') {
                setFormErrors({ name: 'Tag name should not be empty' });
                return;
            }

            if (newTagData.colorCode.trim() === '') {
                setFormErrors({ colorCode: 'Color code should not be empty' });
                return;
            }

            setFormErrors({});

            await tagService.createTag(newTagData);
            closeModal();
            fetchTags();
        } catch (error) {
            setError('Error' + (error as Error).message);
            navigate(-1);
        }
    };

    const confirmDelete = async () => {
        try {
            await tagService.deleteTag(deleteId);
            setTags(prevTags => prevTags.filter(tag => tag.id !== deleteId));
            closeModal();
        } catch (error) {
            setError('Error' + (error as Error).message);
            navigate(-1);
        }
    };

    const closeModal = () => {
        setModalOpen(false);
        setDeleteModalOpen(false);
        setNewTagModalOpen(false);
    };

    return (
        <div className="tags-list Content__Container">
            <h1 className='Tag__Header'>Tags</h1>
            <div className="button-container">
                <Button color='teal' onClick={() => setNewTagModalOpen(true)} className='tag-button'>Create new tag</Button>
            </div>
            {error && <Message negative>{error}</Message>}
            <div className="loader-container">
                {loading && (
                    <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
                        <Loader>Loading</Loader>
                    </Dimmer>
                )}
                <div className='Table__Container'>
                    <Table celled inverted selectable striped>
                        <Table.Header>
                            <Table.Row>
                                <Table.HeaderCell>Tag name</Table.HeaderCell>
                                <Table.HeaderCell>Tag color</Table.HeaderCell>
                                <Table.HeaderCell>Actions</Table.HeaderCell>
                            </Table.Row>
                        </Table.Header>

                        <Table.Body>
                            {tags.map((tag) => (
                                <Table.Row key={tag.id}>
                                    <Table.Cell>{tag.name}</Table.Cell>
                                    <Table.Cell style={{ color: tag.colorCode }}>{tag.colorCode}</Table.Cell>
                                    <Table.Cell>
                                        <Button color="blue" onClick={() => {
                                            setTagData({
                                                id: tag.id,
                                                name: tag.name,
                                                colorCode: tag.colorCode
                                            });
                                            setModalOpen(true);
                                        }}>Update</Button>
                                        <Button negative onClick={() => handleDelete(tag.id)}>Delete</Button>
                                    </Table.Cell>
                                </Table.Row>
                            ))}
                        </Table.Body>
                    </Table>
                    <Modal open={modalOpen} onClose={closeModal} className='modal-wrapper'>
                        <Modal.Header className='modal-header'>Update tag</Modal.Header>
                        <Modal.Content className='modal-content'>
                            <Form>
                                <Form.Input
                                    label='Tag name'
                                    type='text'
                                    value={tagData.name}
                                    onChange={(e) => setTagData({ ...tagData, name: e.target.value })}
                                    error={formErrors['name']}
                                />
                                <Form.Input label='Tag color' error={formErrors['colorCode']}>
                                    <SketchPicker
                                        color={tagData.colorCode}
                                        onChange={(color) => setTagData({ ...tagData, colorCode: color.hex })}
                                    />
                                </Form.Input>
                            </Form>
                        </Modal.Content>
                        <Modal.Actions className='modal-actions'>
                            <Button onClick={closeModal}>Cancel</Button>
                            <Button
                                content="Update"
                                labelPosition='left'
                                icon='checkmark'
                                onClick={(e) => handleUpdate(e, tagData.id)}
                                positive
                            />
                        </Modal.Actions>
                    </Modal>
                    <Modal open={deleteModalOpen} onClose={closeModal} className='modal-wrapper'>
                        <Modal.Header className='modal-header'>Delete tag</Modal.Header>
                        <Modal.Content className='modal-content'>
                            <p>Are you sure you want to delete this tag?</p>
                        </Modal.Content>
                        <Modal.Actions className='modal-actions'>
                            <Button onClick={closeModal}>Cancel</Button>
                            <Button
                                content="Delete"
                                labelPosition='left'
                                icon='trash'
                                onClick={confirmDelete}
                                negative
                            />
                        </Modal.Actions>
                    </Modal>
                    <Modal open={newTagModalOpen} onClose={closeModal} className='modal-wrapper'>
                        <Modal.Header className='modal-header'>New tag</Modal.Header>
                        <Modal.Content className='modal-content'>
                            <Form>
                                <Form.Input
                                    label='Tag name'
                                    type='text'
                                    value={newTagData.name}
                                    onChange={(e) => setNewTagData({ ...newTagData, name: e.target.value })}
                                    error={formErrors['name']}
                                />
                                <Form.Input label='Tag color' error={formErrors['colorCode']}>
                                    <SketchPicker
                                        color={newTagData.colorCode}
                                        onChange={(color) => setNewTagData({ ...newTagData, colorCode: color.hex })}
                                    />
                                </Form.Input>
                            </Form>
                        </Modal.Content>
                        <Modal.Actions className='modal-actions'>
                            <Button onClick={closeModal}>Cancel</Button>
                            <Button
                                content="Create"
                                labelPosition='left'
                                icon='checkmark'
                                onClick={handleNewTagSubmit}
                                positive
                            />
                        
                        </Modal.Actions>
                    </Modal>
                </div>
            </div>
        </div>
    );
};

export default TagsList;