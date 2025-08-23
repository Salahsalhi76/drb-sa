<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="ltr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Registration Driver</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/flowbite@1.3.4/dist/flowbite.js"></script>
</head>

<body>

<div>
<div class="container max-w-screen-lg mx-auto">
        <div class="bg-white rounded shadow-lg p-4 px-4 md:p-8 mb-6">
            <form action="{{route('wasel.driver.store', ['driverId' => $driver] )}}" method="POST" >
				@csrf
                <div class="grid gap-4 gap-y-2 text-sm grid-cols-1 md:grid-cols-5">
                    <div class="md:col-span-5">
                        <label for="identityNumber">Identifier</label>
                        <input type="text" name="identityNumber" id="identityNumber"
                            class="h-10 border mt-1 rounded px-4 w-full bg-gray-50" value="" required />
                    </div>

                    <div class="md:col-span-5">
                        <label for="dateOfBirthGregorian">Date Of Birth</label>
                        <input type="date" name="dateOfBirthGregorian" id="dateOfBirthGregorian"
                            class="h-10 border mt-1 rounded px-4 w-full bg-gray-50" value=""
                            placeholder="email@domain.com" required />
                    </div>



                    <div class="md:col-span-5 ">
                        <label for="sequenceNumber">Sequence Number</label>
                        <input type="text" name="sequenceNumber" id="sequenceNumber"
                            class="h-10 border mt-1 rounded px-4 w-full bg-gray-50" value="" placeholder=""  required/>
                    </div>

                    <div class="md:col-span-1">
                        <label for="plateLetterRight">plateLetterRight</label>
                        <div class="h-10 bg-gray-50 flex border border-gray-200 rounded items-center mt-1">
                            <input name="plateLetterRight" id="plateLetterRight" placeholder="plateLetterRight"
                                class="px-4 appearance-none outline-none text-gray-800 w-full bg-transparent"
                                value="" required />
                        </div>
                    </div>

                    <div class="md:col-span-1">
                        <label for="plateLetterMiddle">plateLetterMiddle</label>
                        <div class="h-10 bg-gray-50 flex border border-gray-200 rounded items-center mt-1">
                            <input name="plateLetterMiddle" id="plateLetterMiddle" placeholder="plateLetterMiddle"
                                class="px-4 appearance-none outline-none text-gray-800 w-full bg-transparent"
                                value="" required />
                        </div>
                    </div>
                    <div class="md:col-span-1">
                        <label for="plateLetterLeft">plateLetterLeft</label>
                        <div class="h-10 bg-gray-50 flex border border-gray-200 rounded items-center mt-1">
                            <input name="plateLetterLeft" id="plateLetterLeft" placeholder="plateLetterLeft"
                                class="px-4 appearance-none outline-none text-gray-800 w-full bg-transparent"
                                value="" required />
                        </div>
                    </div>
                    <div class="md:col-span-1">
                        <label for="plateNumber">plateNumber</label>
                        <div class="h-10 bg-gray-50 flex border border-gray-200 rounded items-center mt-1">
                            <input name="plateNumber" id="plateNumber" placeholder="plateNumber"
                                class="px-4 appearance-none outline-none text-gray-800 w-full bg-transparent"
                                value="" required />
                        </div>
                    </div>
                    <div class="md:col-span-1">
                        <label for="plateType">plateType</label>
                        <div class="h-10 bg-gray-50 flex border border-gray-200 rounded items-center mt-1">
                            <input name="plateType" id="plateType" placeholder="plateType"
                                class="px-4 appearance-none outline-none text-gray-800 w-full bg-transparent"
                                value="" required />
                        </div>
                    </div>
                    <div class="md:col-span-5 text-right">
                        <div class="inline-flex items-end">
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Register
                            </button>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>
  


</body>

</html>
