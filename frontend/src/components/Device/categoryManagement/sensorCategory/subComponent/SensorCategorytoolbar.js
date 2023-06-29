import React from 'react';
import Typography from '@mui/material/Typography';
import Box from '@mui/material/Box';
import Stack from '@mui/material/Stack';
import Fab from '@mui/material/Fab';
import AddIcon from '@mui/icons-material/Add';

export function SensorCategorytoolbar(props) {
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
       sx={{ m: 0, fontFamily: 'customfont', fontSize: '22px', fontWeight: '600', letterSpacing: '1px' }}
        variant="h5"
      >
        Sensor Category
      </Typography>
      {props.userAccess.add && (
        <Box
          sx={{ m: 0 }}
          onClick={() => {
            props.setIsAddButton(true);
            props.setEditCategory([]);
            props.setOpen(true);
          }}
        >
          <Stack direction="row" spacing={2} className='mt-3 sm:mt-0'>
            <Fab 
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
                fontWeight: '600',  }}            >
              <AddIcon sx={{ mr: 1 }} />
              Add Category
            </Fab>
          </Stack>
        </Box>
      )}
    </Box>
  );
}
