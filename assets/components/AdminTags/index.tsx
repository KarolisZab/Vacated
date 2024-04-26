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
            await tagService.createTag(newTagData);
            closeModal();
            fetchTags();
        } catch (error) {
            setError('Error' + (error as Error).message);
            navigate("/");
        }
    };

    const confirmDelete = async () => {
        try {
            await tagService.deleteTag(deleteId);
            setTags(prevTags => prevTags.filter(tag => tag.id !== id));
            closeModal();
        } catch (error) {
            setError('Error' + (error as Error).message);
            navigate("/");
        }
    };

    const closeModal = () => {
        setModalOpen(false);
        setDeleteModalOpen(false);
        setNewTagModalOpen(false);
    };

    return (
        <div className="tags-list">
            <h1>Tags</h1>
            <Button color='teal' onClick={() => setNewTagModalOpen(true)} className='tag-button'>Create new tag</Button>
            {error && <Message negative>{error}</Message>}
            <div className="loader-container">
                {loading && (
                    <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
                        <Loader>Loading</Loader>
                    </Dimmer>
                )}
                <div style={{ marginRight: '2rem' }}>
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
                    <Modal open={modalOpen} onClose={closeModal}>
                        <Modal.Header>Update tag</Modal.Header>
                        <Modal.Content>
                            <Form>
                                <Form.Input
                                    label='Tag name'
                                    value={tagData.name}
                                    onChange={(e) => setTagData({ ...tagData, name: e.target.value })}
                                />
                                <div>
                                    <label>Tag color</label>
                                    <SketchPicker
                                        color={tagData.colorCode}
                                        onChange={(color) => setTagData({ ...tagData, colorCode: color.hex })}
                                    />
                                </div>
                            </Form>
                        </Modal.Content>
                        <Modal.Actions>
                            <Button color='black' onClick={closeModal}>Cancel</Button>
                            <Button
                                content="Update"
                                labelPosition='left'
                                icon='checkmark'
                                onClick={(e) => handleUpdate(e, tagData.id)}
                                positive
                            />
                        </Modal.Actions>
                    </Modal>
                    <Modal open={deleteModalOpen} onClose={closeModal}>
                        <Modal.Header>Delete tag</Modal.Header>
                        <Modal.Content>
                            <p style={{ color: 'black' }}>Are you sure you want to delete this tag?</p>
                        </Modal.Content>
                        <Modal.Actions>
                            <Button color='black' onClick={closeModal}>Cancel</Button>
                            <Button
                                content="Delete"
                                labelPosition='left'
                                icon='trash'
                                onClick={confirmDelete}
                                negative
                            />
                        </Modal.Actions>
                    </Modal>
                    <Modal open={newTagModalOpen} onClose={closeModal}>
                        <Modal.Header>New tag</Modal.Header>
                        <Modal.Content>
                            <Form>
                                <Form.Input
                                    label='Tag name'
                                    value={newTagData.name}
                                    onChange={(e) => setNewTagData({ ...newTagData, name: e.target.value })}
                                />
                                <div>
                                    <label>Tag color</label>
                                    <SketchPicker
                                        color={newTagData.colorCode}
                                        onChange={(color) => setNewTagData({ ...newTagData, colorCode: color.hex })}
                                    />
                                </div>
                            </Form>
                        </Modal.Content>
                        <Modal.Actions>
                            <Button color='black' onClick={closeModal}>Cancel</Button>
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