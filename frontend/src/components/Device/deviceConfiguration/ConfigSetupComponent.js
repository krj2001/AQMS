import React from 'react';
import { Box, Container, Grid } from '@mui/material';
import { ConfigSetupListResults } from './subcomponent/ConfigSetupListResults';

function ConfigSetupComponent(props) {
  return (
    <Container maxWidth={false} style={{ marginTop: 0, padding: '5px' }}>
      <Grid
        sx={{ marginTop: 0, padding: 0 }}
        item
        xs={12}
        sm={12}
        md={12}
        lg={12}
        xl={12}
      >
        <ConfigSetupListResults />
      </Grid>
    </Container>
  );
}

export default ConfigSetupComponent;
