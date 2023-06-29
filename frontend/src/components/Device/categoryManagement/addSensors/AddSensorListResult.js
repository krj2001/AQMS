import React, { useState } from 'react';
import PropTypes from 'prop-types';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import { Container } from '@mui/material';
import AddSensorManagement from './AddSensorManagement';

function AddSensorListResult() {
  return (
    <div className={'mt-0 p-0 w-full'}>
      <Container maxWidth={false} style={{ padding: 0, marginTop: 0 }}>
        <AddSensorManagement />
      </Container>
    </div>
  );
}

export default AddSensorListResult;
