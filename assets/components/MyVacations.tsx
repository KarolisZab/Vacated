import { Message, Tab } from 'semantic-ui-react';
import '../styles/my-vacations.scss';
import { useEffect, useState } from 'react';
import { VacationType } from '../services/types';
import vacationService from '../services/vacation-service';
import RequestedVacations from './RequestedVacations';
import ConfirmedVacations from './ConfirmedVacations';
import RejectedVacations from './RejectedVacations';
import { useNavigate } from 'react-router-dom';
import UpcomingVacations from './UpcomingVacations';

export default function MyVacations() {
    const navigate = useNavigate();
    const [requestedVacations, setRequestedVacations] = useState<VacationType[]>([]);
    const [confirmedVacations, setConfirmedVacations] = useState<VacationType[]>([]);
    const [rejectedVacations, setRejectedVacations] = useState<VacationType[]>([]);
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(true);
    const [upcomingVacations, setUpcomingVacations] = useState<VacationType[]>([]);

    useEffect(() => {
        const fetchVacations = async () => {
            try {
                const requested = await vacationService.getAllCurrentUserVacations('requested');
                const confirmed = await vacationService.getAllCurrentUserVacations('confirmed');
                const rejected = await vacationService.getAllCurrentUserVacations('rejected');
                const upcoming = await vacationService.getAllCurrentUserVacations('upcoming');
                setRequestedVacations(requested);
                setConfirmedVacations(confirmed);
                setRejectedVacations(rejected);
                setUpcomingVacations(upcoming);
            } catch (error) {
                navigate('/login');
            } finally {
                setLoading(false);
            }
        };

        fetchVacations();
    }, []);

    const updateVacations = async () => {
        try {
            const requested = await vacationService.getAllCurrentUserVacations('requested');
            const confirmed = await vacationService.getAllCurrentUserVacations('confirmed');
            const rejected = await vacationService.getAllCurrentUserVacations('rejected');
            const upcoming = await vacationService.getAllCurrentUserVacations('upcoming');
            setRequestedVacations(requested);
            setConfirmedVacations(confirmed);
            setRejectedVacations(rejected)
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
            <h1>My Vacations</h1>
            <Tab panes={panes} />
        </div>
    );
}