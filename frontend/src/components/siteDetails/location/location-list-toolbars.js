import {
  Box,
  Typography,
} from '@mui/material';

import Stack from '@mui/material/Stack';
import Fab from '@mui/material/Fab';
import AddIcon from '@mui/icons-material/Add';

export function LocationListToolbar(props) {
  return (
    <Box className={'h-[auto] min-h-[6px]'}
      sx={{
        alignItems: 'center',
        display: 'flex',
        justifyContent: 'space-between',
        flexWrap: 'wrap',
      }}
    >
      <Typography
        sx={{ m: 1, fontSize: '20px', letterSpacing: '1px', fontWeight: '600', fontFamily: 'customfont' }}
        variant="h5"
      >
        Location
      </Typography>
      {props.userAccess.add && (
        <Box
          sx={{ m: 1 }}
          onClick={() => {
            props.setIsAddButton(true);
            props.setEditCustomer([]);
            props.setOpen(true);
          }}
        >
          <Stack direction="row" spacing={2}>
            <Fab variant="extended"
              style={{ background: 'rgb(19 60 129)'}}
              sx={{
                height: '0',
                color: 'white',
                padding: "10px 19px",
               
                fontSize: '13px',
                borderRadius: '10px',
                fontWeight: '600',
                fontFamily: 'customfont',
                letterSpacing: '1px',
                boxShadow: 'none'
              }}
            >
              <AddIcon sx={{ mr: 1 }} />
              Add Location
            </Fab>
          </Stack>
        </Box>
      )}
    </Box>
  );
}
