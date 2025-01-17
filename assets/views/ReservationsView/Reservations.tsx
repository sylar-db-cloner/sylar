import { useEffect, useState } from 'react';
import { styled } from '@mui/material/styles';
import clsx from 'clsx';
import PerfectScrollbar from 'react-perfect-scrollbar';

import {
  Box,
  Button,
  Card,
  CardHeader,
  Divider,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
} from '@mui/material';
import ReplayIcon from '@mui/icons-material/Replay';
import DeleteIcon from '@mui/icons-material/Delete';
import queryReservations from '../../graphQL/Reservation/queryReservations';
import mutationDeleteReservation from '../../graphQL/Reservation/mutationDeleteReservation';
import { ReservationsQuery } from '../../gql/graphql';
import { ArrElement } from '../../components/Helper';
import { useAuthenticatedClient } from '../../Context/Authentication/AuthenticatedClient';

const PREFIX = 'Reservations';

const classes = {
  root: `${PREFIX}-root`,
  value: `${PREFIX}-value`,
  actions: `${PREFIX}-actions`,
};

const StyledCard = styled(Card)(() => ({
  [`&.${classes.root}`]: {},

  [`& .${classes.value}`]: {
    display: 'inline-block',
  },

  [`& .${classes.actions}`]: {
    justifyContent: 'flex-end',
  },
}));

const Reservations = ({ refresh, ...rest }: { refresh: string }) => {
  const [data, setData] = useState<ReservationsQuery['reservations']>([]);
  const [loading, setLoading] = useState(false);
  const { client } = useAuthenticatedClient();

  const loadReservations = () => {
    setLoading(true);
    return queryReservations(client).then((result) => {
      setData(result);
      setLoading(false);
    });
  };

  const deleteReservation = (
    reservation: ArrElement<ReservationsQuery['reservations']>,
  ) => {
    setLoading(true);
    return mutationDeleteReservation(
      client,
      reservation.service,
      reservation.name,
      reservation.index,
    ).then(() => {
      setLoading(false);
      loadReservations().then(() => {});
    });
  };

  useEffect(() => {
    loadReservations().then(() => {});
  }, []);

  useEffect(() => {
    loadReservations().then(() => {});
  }, [refresh]);

  return (
    <StyledCard className={clsx(classes.root)} {...rest}>
      <CardHeader title="Reservations" />
      <Divider />
      <PerfectScrollbar>
        <Box minWidth={800}>
          <Table size="small">
            <TableHead>
              <TableRow>
                <TableCell>{loading ? 'Loading' : 'Service'}</TableCell>
                <TableCell>Name</TableCell>
                <TableCell>Index</TableCell>
                <TableCell>
                  <Button onClick={loadReservations}>
                    <ReplayIcon />
                  </Button>
                </TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {data.map((reservation) => (
                <TableRow
                  hover
                  key={`${reservation.service}-${reservation.name}`}
                >
                  <TableCell style={{ verticalAlign: 'top' }}>
                    {reservation.service}
                  </TableCell>
                  <TableCell style={{ verticalAlign: 'top' }}>
                    {reservation.name}
                  </TableCell>
                  <TableCell style={{ verticalAlign: 'top' }}>
                    {reservation.index}
                  </TableCell>
                  <Button onClick={() => deleteReservation(reservation)}>
                    <DeleteIcon />
                  </Button>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </Box>
      </PerfectScrollbar>
    </StyledCard>
  );
};

export default Reservations;
