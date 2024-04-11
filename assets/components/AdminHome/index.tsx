import { Card, Icon, Segment, Statistic } from "semantic-ui-react";
import './styles.scss';

export default function Home() {

    return (
        <div className="admin-home">
            <div className="admin-container">
                <div className="admin-card-container">
                    <Card className="card">
                        <Card.Content>
                            <div className="admin-card-header">
                                <Card.Header>Statistics</Card.Header>
                                <Card.Meta>This year</Card.Meta>
                            </div>
                            <div className="admin-card-content">
                                <Card.Description>
                                    <Segment inverted>
                                        <Statistic.Group inverted className="admin-statistics-wrapper">
                                            <Statistic>
                                                <Statistic.Value>
                                                    <Icon name="check" size="small" />
                                                    <span>45</span>
                                                </Statistic.Value>
                                                <Statistic.Label>Confirmed days</Statistic.Label>
                                            </Statistic>
                                            <Statistic>
                                                <Statistic.Value>
                                                    <Icon name="exclamation circle" size="small" />20
                                                </Statistic.Value>
                                                <Statistic.Label>Pending requests</Statistic.Label>
                                            </Statistic>
                                            <Statistic>
                                                <Statistic.Value>
                                                    <Icon name="minus circle" size="small"  />5
                                                </Statistic.Value>
                                                <Statistic.Label>Reserved days</Statistic.Label>
                                            </Statistic>
                                            <Statistic>
                                                <Statistic.Value>
                                                    <Icon name="users" size="small"  />
                                                    42
                                                </Statistic.Value>
                                                <Statistic.Label>Employees</Statistic.Label>
                                            </Statistic>
                                        </Statistic.Group>
                                    </Segment>
                                </Card.Description>
                            </div>
                            <div className="admin-card-header" />
                        </Card.Content>
                    </Card>
                </div>
            </div>
        </div>
    );
}