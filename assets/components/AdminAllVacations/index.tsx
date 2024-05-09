import { Message, Tab } from 'semantic-ui-react';
import './styles.scss';
import { useEffect, useState } from 'react';
import { VacationType } from '../../services/types';
import vacationService from '../../services/vacation-service';
import RequestedVacations from './RequestedVacations';
import ConfirmedVacations from './ConfirmedVacations';
import RejectedVacations from './RejectedVacations';
import UpcomingVacations from './UpcomingVacations';
import { useNavigate } from 'react-router-dom';

export default function MyVacations() {
    const navigate = useNavigate();
    const [requestedVacations, setRequestedVacations] = useState<VacationType[]>([]);
    const [confirmedVacations, setConfirmedVacations] = useState<VacationType[]>([]);
    const [rejectedVacations, setRejectedVacations] = useState<VacationType[]>([]);
    const [upcomingVacations, setUpcomingVacations] = useState<VacationType[]>([]);
    const [loading, setLoading] = useState<boolean>(true);
    const [error, setError] = useState<string>('');

    useEffect(() => {
        const fetchVacations = async () => {
            try {
                const requested = await vacationService.getAllVacations('requested');
                const confirmed = await vacationService.getAllVacations('confirmed');
                const rejected = await vacationService.getAllVacations('rejected');
                const upcoming = await vacationService.getAllVacations('upcoming');
                setRequestedVacations(requested);
                setConfirmedVacations(confirmed);
                setRejectedVacations(rejected);
                setUpcomingVacations(upcoming);
            } catch (error) {
                navigate('/');
            } finally {
                setLoading(false);
            }
        };

        fetchVacations();
    }, []);

    const updateVacations = async () => {
        try {
            const requested = await vacationService.getAllVacations('requested');
            const confirmed = await vacationService.getAllVacations('confirmed');
            const rejected = await vacationService.getAllVacations('rejected');
            const upcoming = await vacationService.getAllVacations('upcoming');
            setRequestedVacations(requested);
            setConfirmedVacations(confirmed);
            setRejectedVacations(rejected);
            setUpcomingVacations(upcoming);
        } catch (error) {
            setError('Error' + (error as Error).message);
        }
    };
    
    const panes = [
        { menuItem: 'Requested', render: () => (
            <Tab.Pane loading={loading}>
                <RequestedVacations vacations={requestedVacations} updateVacations={updateVacations} />
            </Tab.Pane> 
        ) },
        { menuItem: 'Confirmed', render: () => (
            <Tab.Pane loading={loading}>
                <ConfirmedVacations vacations={confirmedVacations} updateVacations={updateVacations}/>
            </Tab.Pane>
        ) },
        { menuItem: 'Rejected', render: () => (
            <Tab.Pane loading={loading}>
                <RejectedVacations vacations={rejectedVacations} updateVacations={updateVacations}/>
            </Tab.Pane> 
        ) },
        { menuItem: 'Upcoming / Ongoing', render: () => (
            <Tab.Pane loading={loading}>
                <UpcomingVacations vacations={upcomingVacations} updateVacations={updateVacations}/>
            </Tab.Pane> 
        ) },
    ];

    return (
        <div className="tab-container Content__Container">
            {error && <Message negative>{error}</Message>}
            <h1>Vacations</h1>
            <Tab panes={panes} />
        </div>
    );
}