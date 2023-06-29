import React from 'react';
import { Box, Container, Grid } from '@mui/material';
import DeviceListResults from './subComponent/DeviceListResults';

function DeviceManagementComponent() {
  return (
    <Container maxWidth={true} sx={{ marginTop: 0}} style={{
      padding: '0px',
    }}>
      <Grid
        sx={{ marginTop: 0, padding: 0 }}
        item
        xs={12}
        sm={12}
        md={12}
        lg={12}
        xl={12}
      >
        <DeviceListResults />
      </Grid>
    </Container>
  );
}

export default DeviceManagementComponent;
