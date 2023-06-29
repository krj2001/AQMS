import {
    Box,
    Fab,
    Stack,
    Typography,
  } from '@mui/material';
  
  import AddIcon from '@mui/icons-material/Add';
  import RemoveIcon from '@mui/icons-material/Remove';
  
  export function GlobalHooter(props) {
    return (
      <Box
          style={{
          height: '6vh',
          minHeight: '60px',
          alignItems: 'center',
          display: 'flex',
          flexWrap: 'wrap',
          flexDirection: 'row-reverse',
        }}
      >
        <div style={{display:'flex'}}>        
          <Box
            sx={{ m: 1 }}
            onClick={() => {
                props.setOpenCentralHooter(true);
            }}
          >
            <Stack direction="row" spacing={2}>
              <Fab variant="extended" size="medium" color={props.colorValue} >
                {
                  props.centralButtonText ==="REMOVE CENTRALIZED HOOTER" ? (
                    <RemoveIcon sx={{ mr: 1 }} />
                  ):(
                    <AddIcon sx={{ mr: 1 }} />
                  )
  
                }
                
                {props.centralButtonText}
              </Fab>
            </Stack>
          </Box>
               
        </div>
       
      </Box>
    );
  }
  