import { Box, Typography } from '@mui/material';
import Stack from '@mui/material/Stack';
import Fab from '@mui/material/Fab';
import AddIcon from '@mui/icons-material/Add';

export function CustomerListToolbar(props) {
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
        sx={{ m: 1,
        fontFamily:'customfont', fontWeight:'600', letterSpacing:'1px' }}
        variant="h5"
      >
        Customers
      </Typography>
      <Box
        sx={{ m: 1 }}
        onClick={() => {
          props.setIsAddButton(true);
          props.setEditCustomer([]);
          props.setOpen(true);
        }}
      >
        <Stack direction="row" spacing={2}>
          <Fab 
          sx={{
            height: '0',
            width:'100%',
            padding: "10px 19px",
            color: 'white',
            marginTop: '20px',
            marginBottom: '15px',
            fontSize: '13px',
            borderRadius: '10px',
            fontWeight: '600',
            fontFamily: 'customfont',
            letterSpacing: '1px'
          }}
          style={{
            background: 'rgb(19, 60, 129)',}}
          aria-label="add">
            <AddIcon sx={{ mr: 1 }} />
            Add Customer
          </Fab>
        </Stack>
      </Box>
    </Box>
  );
}
