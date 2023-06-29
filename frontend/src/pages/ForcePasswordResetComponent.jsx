import { Grid } from '@mui/material'
import React from 'react'
import { useEffect } from 'react'
import { useNavigate } from 'react-router-dom'
import UserResetPassword from '../components/UserResetPassword'
import ApplicationStore from '../utils/localStorageUtil'

const ForcePasswordReset = () => {
  const navigate = useNavigate();
  useEffect(()=>{
    const { userDetails } = ApplicationStore().getStorage('userDetails');
    return userDetails?.forcePasswordReset === 1 ? {}: 
    userDetails?.secondLevelAuthorization === 'true' ? navigate('/login') :
    userDetails?.userRole === 'superAdmin' ? navigate('/UserManagement') : navigate('/Dashboard') ;
  }, []);
  return (
    <Grid
      container
      spacing={0}
      alignItems="center"
      justifyContent="center"
    >
      <Grid item xs={12} sm={6}>
        <UserResetPassword padding={70}/>
      </Grid>   
      
    </Grid> 
  )
}

export default ForcePasswordReset