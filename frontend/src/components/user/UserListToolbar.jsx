import {
  Box,
  Typography,
} from '@mui/material';

import Stack from '@mui/material/Stack';
import Fab from '@mui/material/Fab';
import AddIcon from '@mui/icons-material/Add';

export default function UserListToolbar(props) {
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
        fontFamily={'customfont'}
        fontSize={'19px'}
        fontWeight={'600'}
        lineHeight={'1px'}
        padding={'0px'}
        color={'black'}
      >
        User Management
      </Typography>
      {props.userAccess.add && (
        <Box sx={{ m: 1, }}>
        <Stack direction="row" spacing={2} className='min-[320px]:mt-10 min-[768px]:mt-0'>
          <Fab

            style={{
              background: 'rgb(19 60 129)',}}
            sx={{
              width: '100%',
              height: '0',
              color: 'white',
              fontFamily: 'customfont',
              padding: '10px 19px',
              borderRadius: '12px',
              boxShadow: 'none',
              letterSpacing: '1px',
              fontWeight: '600'
            }}
            aria-label="add"
            onClick={() => {
              props.setIsAddButton(true);
              props.setEditUser([]);
              props.setOpen(true);
            }}
          >
            <AddIcon sx={{ mr: 1 }} />
            Add User
          </Fab>
        </Stack>
      </Box>
      )}
    </Box>
  );
}
