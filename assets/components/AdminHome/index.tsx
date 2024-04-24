import { Card, Dimmer, Icon, Loader, Message, Segment, Statistic } from "semantic-ui-react";
import './styles.scss';
import { useEffect, useState } from "react";
import vacationService from "../../services/vacation-service";
import employeeService from "../../services/employee-service";
import reservedDayService from "../../services/reserved-day-service";
import { Chart } from "react-google-charts";

export default function Home() {
    const [confirmedDays, setConfirmedDays] = useState<number>(0);
    const [pendingDays, setPendingDays] = useState<number>(0);
    const [employeeCount, setEmployeeCount] = useState<number>(0);
    const [reservedDays, setReservedDays] = useState<number>(0);
    const [error, setError] = useState<string>('');
    const [loading, setLoading] = useState<boolean>(true);
    const [chartData, setChartData] = useState<any[][]>([]);

    useEffect(() => {
        const fetchStatistics = async () => {
            try {
                const [confirmedDays, pendingDays, reservedDays, employeeCount, monthlyVacationStatistics] = await Promise.all([
                    vacationService.getConfirmedVacationsDaysCountInThisYear(),
                    vacationService.getPendingVacationsDaysCountInThisYear(),
                    reservedDayService.getReservedDaysCount(),
                    employeeService.getEmployeesCount(),
                    vacationService.getMonthlyVacationStatistics()
                ]);

                setConfirmedDays(confirmedDays);
                setPendingDays(pendingDays);
                setReservedDays(reservedDays);
                setEmployeeCount(employeeCount);
                console.log(monthlyVacationStatistics);

                const chartData = [["Month", "Days"]];
                for (const [month, daysCount] of Object.entries(monthlyVacationStatistics)) {
                    chartData.push([month, daysCount]);
                }
                setChartData(chartData);
            } catch (error) {
                setError('Error' + (error as Error).message);
            } finally {
                setLoading(false);
            }
        }

        fetchStatistics();
    }, [])

    return (
        <div className="admin-home">
            <div className="admin-container">
                {error && <Message negative>{error}</Message>}
                <div className="admin-card-container">
                    <Card className="card">
                        {loading && (
                            <Dimmer active style={{ backgroundColor: 'rgb(31, 31, 32)' }}>
                                <Loader>Loading</Loader>
                            </Dimmer>
                        )}
                        <Card.Content>
                            <div className="admin-card-header">
                                <Card.Header>Statistics</Card.Header>
                            </div>
                            <div className="admin-card-content">
                                <Card.Description>
                                    <Segment inverted>
                                        <Statistic.Group inverted className="admin-statistics-wrapper">
                                            <Statistic>
                                                <Statistic.Value>
                                                    <Icon name="check" size="small" />
                                                    <span>{confirmedDays}</span>
                                                </Statistic.Value>
                                                <Statistic.Label>Confirmed days</Statistic.Label>
                                            </Statistic>
                                            <Statistic>
                                                <Statistic.Value>
                                                    <Icon name="exclamation circle" size="small" />
                                                    {pendingDays}
                                                </Statistic.Value>
                                                <Statistic.Label>Pending requests</Statistic.Label>
                                            </Statistic>
                                            <Statistic>
                                                <Statistic.Value>
                                                    <Icon name="minus circle" size="small"  />
                                                    {reservedDays}
                                                </Statistic.Value>
                                                <Statistic.Label>Reserved days</Statistic.Label>
                                            </Statistic>
                                            <Statistic>
                                                <Statistic.Value>
                                                    <Icon name="users" size="small"  />
                                                    {employeeCount}
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
                <div className="admin-chart-container">
                    <Chart
                        chartType="ColumnChart"
                        width="100%"
                        height="400px"
                        data={chartData}
                        options={{
                            title: "Monthly Vacation Statistics",
                            titleTextStyle: { color: '#FFF' },
                            legend: { position: "none" },
                            hAxis: { title: "Month", textStyle:{color: '#FFF'}, titleTextStyle: { color: '#FFF' }, },
                            vAxis: { title: "Confirmed vacation days", textStyle:{color: '#FFF'}, titleTextStyle: { color: '#FFF' }, },
                            backgroundColor: 'rgb(31, 31, 32)',
                            colors: ['#FB7A21'],
                        }}
                    />
                </div>
            </div>
        </div>
    );
}