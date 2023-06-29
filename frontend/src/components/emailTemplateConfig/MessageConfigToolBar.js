import { Edit, EditOff } from '@mui/icons-material';
import { Box, Button, Fab, Grid, Stack, Typography } from '@mui/material';
import React from 'react'

const MessageConfigToolBar = ({isEdit, setisEdit}) => {
  return (
    <Grid
      style={{
        display: 'flex',
        width: '100%',
        flexWrap: 'nowrap',
        justifyContent: 'space-between'
      }}
    >
      <Typography
        sx={{ m: 1 }}
        variant="h6"
      >
        Message Template
      </Typography>
      {/* {props.userAccess.add && ( */}
        <Box
            sx={{ m: 1 }}
            onClick={() => {
            // props.setIsAddButton(true);
            // props.setEditData([]);
            // props.setOpen(true);
            }}
        >
            {/* <Stack direction="row" spacing={2}>
            <Fab variant="extended" size="small" color="primary" aria-label="add">
                {isEdit === true ? <Edit sx={{ mr: 1 }} /> : <EditOff sx={{ mr: 1 }} />}
                Edit
            </Fab>
            </Stack> */}
            <Button 
                variant="outlined" 
                startIcon={isEdit === true ? <Edit sx={{ mr: 1 }} /> : <EditOff sx={{ mr: 1 }} />}
                disabled={!isEdit}
                onClick={()=>{
                    setisEdit(false);
                }}
            >
                Edit
            </Button>
        </Box>
      {/* )} */}
    </Grid>
  )
}

export default MessageConfigToolBar