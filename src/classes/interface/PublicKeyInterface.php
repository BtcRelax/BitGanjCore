<?php
/***********************************************************************
Copyright 2010 Matyas Danter

This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*************************************************************************/

/**
 * This is the contract for the PublicKey portion of ECDSA.
 *
 * @author Matej Danter
 */
interface PublicKeyInterface
{
    public function __construct(Point $generator, Point $point);

    public function verifies($hash, Signature $signature);

    public function getCurve();

    public function getGenerator();

    public function getPoint();
}
