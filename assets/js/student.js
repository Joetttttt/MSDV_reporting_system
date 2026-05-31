function formatStudentID(input)
{
    let value = input.value.replace(/\D/g,'');

    if(value.length > 3)
    {
        value =
        value.substring(0,3)
        + "-"
        + value.substring(3,8);
    }

    input.value = value;
}