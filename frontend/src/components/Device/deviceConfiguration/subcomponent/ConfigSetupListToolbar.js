import React from 'react';
import { Box, Typography } from '@mui/material';
import Stack from '@mui/material/Stack';
import Fab from '@mui/material/Fab';
import AddIcon from '@mui/icons-material/Add';

export function ConfigSetupListToolbar(props) {
  return (
    <Box
      sx={{
        mb: '0px',
        alignItems: 'center',
        display: 'flex',
        justifyContent: 'space-between',
        flexWrap: 'wrap',
      }}
    >
      <Typography
        sx={{ m: 0, fontFamily: 'customfont', fontSize: '22px', fontWeight: '600', letterSpacing: '1px' }}
        variant="h5"
        component="span"
      >
        Config Profiles
      </Typography>
      {props.userAccess.add && (
        <Box
          sx={{ mb: 1 }}
          onClick={() => {
            props.setIsAddButton(true);
            props.setEditConfigSetup([]);
            props.setOpen(true);
          }}
          
        >
          <Stack direction="row" spacing={2} className='mt-5 sm:mt-0'>
            <Fab
              // variant="extended"
              // size="medium"
              // color="primary"
              // aria-label="add"
              style={{
                backgroundColor: 'rgb(19 60 129)'}}
              sx={{ 
                width:"100%",
                height:'0',
                padding:'10px 19px',
              fontFamily: 'customfont', 
              boxShadow: 'none', 
              borderRadius: '10px', 
              color: 'white', 
              fontWeight: '600',  }}
            >
              <AddIcon sx={{ mr: 1 }} />
              Add Config Setup
            </Fab>
          </Stack>
        </Box>
      )}
    </Box>
  );
}
