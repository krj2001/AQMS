import { Add } from '@mui/icons-material';
import {
  Box, Fab, Stack, Typography,
} from '@mui/material';
import React from 'react';

function GasCylinderToolbar({ moduleAccess, setIsAddButton, setOpen }) {
  return (
    <Box
      sx={{
        mb: '10px',
        alignItems: 'center',
        display: 'flex',
        justifyContent: 'space-between',
        flexWrap: 'wrap',
      }}
    >
      <Typography
        sx={{ m: 1 }}
        variant="h5"
      >
        Gas Cylinder
      </Typography>
      {moduleAccess.add && (
        <Box
          sx={{ m: 1 }}
          onClick={() => {
            setIsAddButton(true);
            // props.setEditVendor([]);
            setOpen(true);
          }}
        >
          <Stack direction="row" spacing={2}>
            <Fab variant="extended" size="medium" color="primary" aria-label="add">
              <Add sx={{ mr: 1 }} />
              Add Cylinder
            </Fab>
          </Stack>
        </Box>
      )}
    </Box>
  );
}

export default GasCylinderToolbar;
