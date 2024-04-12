import { Tab } from 'semantic-ui-react';
import './styles.scss';
import { useEffect, useState } from 'react';
import { VacationType } from '../../services/types';
import vacationService from '../../services/vacation-service';
import RequestedVacations from './RequestedVacations';
import ConfirmedVacations from '../../components/ConfirmedVacations';
import RejectedVacations from './RejectedVacations';
import { useNavigate } from 'react-router-dom';

export default function MyVacations() {
    const navigate = useNavigate();
    const [requestedVacations, setRequestedVacations] = useState<VacationType[]>([]);
    const [confirmedVacations, setConfirmedVacations] = useState<VacationType[]>([]);
    const [rejectedVacations, setRejectedVacations] = useState<VacationType[]>([]);
    const [loading, setLoading] = useState<boolean>(true);

    useEffect(() => {
        const fetchVacations = async () => {
            try {
                const allVacations = await vacationService.getAllVacations();
                const requested = allVacations.filter(vacation => !vacation.confirmed && !vacation.rejected);
                const confirmed = allVacations.filter(vacation => vacation.confirmed);
                const rejected = allVacations.filter(vacation => vacation.rejected);
                setRequestedVacations(requested);
                setConfirmedVacations(confirmed);
                setRejectedVacations(rejected);
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
            const allVacations = await vacationService.getAllVacations();
            const requested = allVacations.filter(vacation => !vacation.confirmed && !vacation.rejected);
            const confirmed = allVacations.filter(vacation => vacation.confirmed);
            const rejected = allVacations.filter(vacation => vacation.rejected);
            setRequestedVacations(requested);
            setConfirmedVacations(confirmed);
            setRejectedVacations(rejected);
        } catch (error) {
            console.error('Error fetching vacations:', error);
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
                <RejectedVacations vacations={rejectedVacations}/>
            </Tab.Pane> 
        ) },
    ];

    return (
        <div className="tab-container">
            <Tab panes={panes} />
        </div>
    );
}