import { Edit, EditOff } from '@mui/icons-material';
import { Box, Button, Fab, Grid, Stack, Typography } from '@mui/material';
import React from 'react'

const EmailConfigToolBar = ({ isEdit, setisEdit }) => {
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
        {/* Email Template */}
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

          startIcon={isEdit === true ? <Edit sx={{ mr: 1 }} /> : <EditOff sx={{ mr: 1 }} />}
          disabled={!isEdit}
          onClick={() => {
            setisEdit(false);
          }}
          className=' hover:text-black'
          style={{
            height:'0',
            color:'white',
            padding: "22px 19px",
            background: 'rgb(19 60 129',
            letterSpacing:'1px',
            fontWeight:'600',
            borderRadius:'12px'
          }}
        >
          Edit
        </Button>
      </Box>
      {/* )} */}
    </Grid>
  )
}

export default EmailConfigToolBar